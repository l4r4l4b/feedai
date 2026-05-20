<?php

namespace App\Livewire\Onboarding;

use App\Ai\Agents\OnboardingAgent;
use App\Jobs\AnalyzeImage;
use App\Models\OnboardingSession;
use App\Models\User;
use App\Models\Vendor;
use App\Services\VendorImageIngestor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Models\Conversation as AiConversation;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\Yaml\Yaml;

/**
 * Onboarding chat — two-step send + multi-image + DB-backed history.
 *
 * Send flow:
 *  1. submitPrompt() — fast. Ingests all photos, builds prompt block,
 *     sets $pendingText/$pendingImageUrls for the optimistic user bubble,
 *     clears draft + photos, triggers the second request via $this->js().
 *  2. runAgent() — slow. Calls the AI agent. RememberConversations middleware
 *     writes user + assistant to agent_conversation_messages. Pending is
 *     cleared, feed-updated dispatched.
 *
 * UI effect: browser sees re-render from step 1 (input empty, pending bubble
 * visible, loading dots) BEFORE the agent call starts. Typical chat UX.
 *
 * Multi-image: $photos is an array. Each image is analyzed sync via
 * VendorImageIngestor, each as its own [IMAGE_UPLOADED] block in the prompt.
 *
 * History is reload-proof: rendered from DB via loadMessages() in render().
 */
#[Title('Onboarding')]
class Chat extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $vendorId = 0;

    #[Locked]
    public string $vendorName = '';

    public string $draft = '';

    /** @var array<int, mixed> */
    #[Validate(['photos.*' => 'image|mimes:jpeg,png,webp|max:10240'])]
    public $photos = [];

    /** Optimistic state — shown between submitPrompt and runAgent. */
    public string $pendingText = '';

    /** @var array<int, string> */
    public $pendingImageUrls = [];

    /** Full prompt incl. [IMAGE_UPLOADED]-blocks, used by runAgent. */
    #[Locked]
    public string $queuedPrompt = '';

    /**
     * Media IDs to analyze + inject into the prompt in runAgent. submitPrompt
     * persists images but skips vision analysis so the user bubble appears
     * instantly; runAgent then analyzes each and builds the prompt blocks.
     *
     * @var array<int, int>
     */
    #[Locked]
    public array $queuedMediaIds = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $vendor = $user->vendor;
        abort_unless($vendor instanceof Vendor, 404);

        $this->vendorId = $vendor->id;
        $this->vendorName = (string) $vendor->name;
    }

    /**
     * Step 1 — fast. Persists photos WITHOUT vision analysis, sets pending
     * state with their URLs, clears input. The vendor sees their message +
     * thumbnails immediately. Vision analysis runs in step 2 (runAgent).
     */
    public function submitPrompt(VendorImageIngestor $ingestor): void
    {
        $text = trim($this->draft);
        $photos = array_filter(is_array($this->photos) ? $this->photos : []);

        if ($text === '' && $photos === []) {
            return;
        }

        $imageUrls = [];
        $mediaIds = [];

        if ($photos !== []) {
            $vendor = Vendor::findOrFail($this->vendorId);

            foreach ($photos as $photo) {
                $media = $ingestor->fromUpload(
                    vendor: $vendor,
                    upload: $photo,
                    intent: null,
                    source: 'onboarding-chat',
                    analyzeSync: false,
                );
                $imageUrls[] = $media->getUrl();
                $mediaIds[] = (int) $media->id;
            }
        }

        $this->pendingText = $text;
        $this->pendingImageUrls = $imageUrls;
        $this->queuedPrompt = $text;
        $this->queuedMediaIds = $mediaIds;

        $this->reset('draft', 'photos');

        $this->dispatch('chat-scroll');
        $this->js('$wire.runAgent()');
    }

    /**
     * Step 2 — slow. Calls the AI agent. RememberConversations persists.
     *
     * If the agent called FinalizeOnboarding in this turn (vendor status
     * → live), redirect to the onboarding success page, from which the
     * vendor is guided to payments + inbox test + public feed.
     */
    public function runAgent(): mixed
    {
        if ($this->queuedPrompt === '' && $this->queuedMediaIds === []) {
            return null;
        }

        $text = $this->queuedPrompt;
        $mediaIds = $this->queuedMediaIds;
        $this->queuedPrompt = '';
        $this->queuedMediaIds = [];

        // Vision analysis runs here (not in submitPrompt) so the user bubble
        // already appeared in the previous render. Sync handle() blocks the
        // request but the UI shows the typing indicator while we wait.
        $imageBlocks = [];
        foreach ($mediaIds as $id) {
            $media = Media::find($id);
            if ($media === null) {
                continue;
            }

            if ((string) $media->getCustomProperty('description', '') === '') {
                (new AnalyzeImage($id))->handle();
                $media = $media->fresh() ?? $media;
            }

            $imageBlocks[] = $this->buildImageBlock($media);
        }

        $prompt = $imageBlocks === []
            ? $text
            : trim($text."\n\n".implode("\n\n", $imageBlocks));

        $this->promptAgent($prompt);

        $this->pendingText = '';
        $this->pendingImageUrls = [];

        $this->dispatch('feed-updated');
        $this->dispatch('chat-scroll');

        $vendor = Vendor::find($this->vendorId);
        if ($vendor !== null && $vendor->status === 'live') {
            return $this->redirectRoute('onboarding.complete', navigate: true);
        }

        return null;
    }

    /**
     * Sends an immediate message when the vendor clicks "Discuss in chat"
     * on a component marker. Mirrors Dashboard\FeedChat for consistency.
     */
    #[On('prefill-chat-draft')]
    public function prefillFromComponent(string $component, VendorImageIngestor $ingestor): void
    {
        $this->draft = sprintf(
            "I'd like to edit the %s. What can we change?",
            $this->componentLabel($component),
        );
        $this->submitPrompt($ingestor);
    }

    private function componentLabel(string $type): string
    {
        $path = config_path("feedai/component-schemas/{$type}.yaml");

        if (is_file($path)) {
            try {
                $schema = Yaml::parseFile($path);
                if (is_array($schema) && ! empty($schema['label'])) {
                    return mb_strtolower((string) $schema['label']);
                }
            } catch (\Throwable) {
                // fall through
            }
        }

        return str_replace('_', ' ', $type);
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
        return view('livewire.onboarding.chat', [
            'chatMessages' => $this->loadMessages(),
        ]);
    }

    /**
     * @return Collection<int, array{id:string, role:string, text:string, image_urls:array<int, string>, tool_summary:?string}>
     */
    private function loadMessages(): Collection
    {
        $conversationId = $this->resolveConversationId();

        if ($conversationId === null) {
            return collect();
        }

        $conversation = AiConversation::find($conversationId);

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

    private function resolveConversationId(): ?string
    {
        return OnboardingSession::where('vendor_id', $this->vendorId)
            ->where('status', 'in_progress')
            ->latest()
            ->value('conversation_id');
    }

    private function promptAgent(string $message): void
    {
        $vendor = Vendor::findOrFail($this->vendorId);
        /** @var User $user */
        $user = Auth::user();

        $existing = $this->resolveConversationId();

        $agent = $existing !== null
            ? (new OnboardingAgent($vendor))->continue($existing, as: $user)
            : (new OnboardingAgent($vendor))->forUser($user);

        $response = $agent->prompt($message);

        if ($existing === null && property_exists($response, 'conversationId') && $response->conversationId !== null) {
            OnboardingSession::where('vendor_id', $this->vendorId)
                ->where('status', 'in_progress')
                ->update(['conversation_id' => $response->conversationId]);
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
