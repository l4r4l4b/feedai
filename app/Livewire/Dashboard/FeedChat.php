<?php

namespace App\Livewire\Dashboard;

use App\Ai\Agents\EditAgent;
use App\Models\User;
use App\Models\Vendor;
use App\Services\VendorImageIngestor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Models\Conversation as AiConversation;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Dashboard edit chat — drives the live feed via EditAgent.
 *
 * Mirrors the onboarding chat (two-step send, multi-image, DB-backed history)
 * but persists the agent conversation_id on the vendor row so the thread
 * continues across requests and sessions.
 *
 * Dispatches `feed-updated` so the sibling preview iframe can refresh.
 */
#[Title('Edit chat')]
class FeedChat extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $vendorId = 0;

    public string $draft = '';

    /** @var array<int, mixed> */
    #[Validate(['photos.*' => 'image|mimes:jpeg,png,webp|max:10240'])]
    public $photos = [];

    /** Optimistic state — shown between submitPrompt and runAgent. */
    public string $pendingText = '';

    /** @var array<int, string> */
    public $pendingImageUrls = [];

    /** Full prompt incl. [IMAGE_UPLOADED] blocks, used by runAgent. */
    #[Locked]
    public string $queuedPrompt = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $vendor = $user->vendor;
        abort_unless($vendor instanceof Vendor, 404);

        $this->vendorId = $vendor->id;
    }

    /**
     * Step 1 — fast. Ingest photos, set pending state, clear input.
     * Triggers the second request for the AI call via JS.
     */
    public function submitPrompt(VendorImageIngestor $ingestor): void
    {
        $text = trim($this->draft);
        $photos = array_filter(is_array($this->photos) ? $this->photos : []);

        if ($text === '' && $photos === []) {
            return;
        }

        $imageBlocks = [];
        $imageUrls = [];

        if ($photos !== []) {
            $vendor = Vendor::findOrFail($this->vendorId);

            foreach ($photos as $photo) {
                $media = $ingestor->fromUpload(
                    vendor: $vendor,
                    upload: $photo,
                    intent: null,
                    source: 'dashboard-edit-chat',
                    analyzeSync: true,
                );
                $imageBlocks[] = $this->buildImageBlock($media);
                $imageUrls[] = $media->getUrl();
            }
        }

        $this->pendingText = $text;
        $this->pendingImageUrls = $imageUrls;
        $this->queuedPrompt = $imageBlocks === []
            ? $text
            : trim($text."\n\n".implode("\n\n", $imageBlocks));

        $this->reset('draft', 'photos');

        $this->dispatch('chat-scroll');
        $this->js('$wire.runAgent()');
    }

    /**
     * Step 2 — slow. Calls the EditAgent. Persists conversation_id on first
     * response so subsequent prompts continue the same thread.
     */
    public function runAgent(): void
    {
        if ($this->queuedPrompt === '') {
            return;
        }

        $prompt = $this->queuedPrompt;
        $this->queuedPrompt = '';

        $this->promptAgent($prompt);

        $this->pendingText = '';
        $this->pendingImageUrls = [];

        $this->dispatch('feed-updated');
        $this->dispatch('chat-scroll');
    }

    public function removePhoto(int $index): void
    {
        if (! is_array($this->photos)) {
            return;
        }

        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function render(): View
    {
        return view('livewire.dashboard.feed-chat', [
            'chatMessages' => $this->loadMessages(),
        ]);
    }

    /**
     * @return Collection<int, array{id:string, role:string, text:string, image_urls:array<int, string>, tool_summary:?string}>
     */
    private function loadMessages(): Collection
    {
        $vendor = Vendor::find($this->vendorId);

        if ($vendor === null || $vendor->edit_agent_conversation_id === null) {
            return collect();
        }

        $conversation = AiConversation::find($vendor->edit_agent_conversation_id);

        if ($conversation === null) {
            return collect();
        }

        return $conversation->messages()
            ->orderBy('created_at')
            ->whereIn('role', ['user', 'assistant'])
            ->get()
            ->map(fn ($msg) => [
                'id' => (string) $msg->id,
                'role' => (string) $msg->role,
                'text' => $this->stripImageBlocks((string) $msg->content),
                'image_urls' => $this->extractImageUrls((string) $msg->content),
                'tool_summary' => $this->summarizeToolCalls($msg->tool_calls ?? []),
            ])
            ->reject(
                fn (array $entry): bool => $entry['text'] === ''
                    && $entry['image_urls'] === []
                    && $entry['tool_summary'] === null
            )
            ->values();
    }

    private function promptAgent(string $message): void
    {
        $vendor = Vendor::findOrFail($this->vendorId);
        /** @var User $user */
        $user = Auth::user();

        $existing = $vendor->edit_agent_conversation_id;

        $agent = $existing !== null
            ? (new EditAgent($vendor))->continue($existing, as: $user)
            : (new EditAgent($vendor))->forUser($user);

        $response = $agent->prompt($message);

        if ($existing === null && property_exists($response, 'conversationId') && $response->conversationId !== null) {
            $vendor->forceFill(['edit_agent_conversation_id' => $response->conversationId])->save();
        }
    }

    private function buildImageBlock(Media $media): string
    {
        $url = $media->getUrl();
        $description = (string) $media->getCustomProperty('description', '');
        $altText = (string) $media->getCustomProperty('alt_text', '');
        $suggestedIntent = (string) $media->getCustomProperty('suggested_intent', 'other');
        $tags = (array) $media->getCustomProperty('tags', []);
        $tagsLine = $tags === [] ? '' : ' tags='.implode(',', $tags);

        return "[IMAGE_UPLOADED url={$url} suggested_intent={$suggestedIntent}{$tagsLine}]"
            ."\nVision description: {$description}"
            ."\nAlt text: {$altText}";
    }

    private function stripImageBlocks(string $content): string
    {
        $clean = preg_replace('/\[IMAGE_UPLOADED[^\n]*\n[^\n]*\n[^\n]*/', '', $content) ?? $content;

        return trim($clean);
    }

    /**
     * @return array<int, string>
     */
    private function extractImageUrls(string $content): array
    {
        if (preg_match_all('/\[IMAGE_UPLOADED url=(\S+)/', $content, $matches)) {
            return $matches[1];
        }

        return [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $toolCalls
     */
    private function summarizeToolCalls(array $toolCalls): ?string
    {
        if ($toolCalls === []) {
            return null;
        }

        $names = array_filter(array_map(
            fn (array $call): ?string => isset($call['name']) ? (string) $call['name'] : null,
            $toolCalls,
        ));

        return $names === [] ? null : '✓ '.implode(', ', $names);
    }
}
