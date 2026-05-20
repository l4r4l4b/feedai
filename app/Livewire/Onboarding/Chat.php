<?php

namespace App\Livewire\Onboarding;

use App\Ai\Agents\OnboardingAgent;
use App\Models\OnboardingSession;
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
 * Onboarding-Chat — Two-Step Send + Multi-Image + DB-backed History.
 *
 * Send-Flow:
 *  1. submitPrompt() — schnell. Ingestiert alle Photos, baut Prompt-Block,
 *     setzt $pendingText/$pendingImageUrls für die optimistische User-Bubble,
 *     leert Draft + Photos, triggert via $this->js() den zweiten Request.
 *  2. runAgent() — langsam. Ruft den AI-Agent. RememberConversations-Middleware
 *     schreibt user + assistant in agent_conversation_messages. Pending wird
 *     geleert, feed-updated dispatched.
 *
 * UI-Effekt: Browser sieht Re-Render von Schritt 1 (Input leer, Pending-Bubble
 * sichtbar, Loading-Dots) BEVOR der Agent-Call startet. Typischer Chat-UX.
 *
 * Multi-Image: $photos ist ein Array. Jedes Bild durch VendorImageIngestor
 * sync analyzed, jedes als eigener [IMAGE_UPLOADED]-Block im Prompt.
 *
 * History reload-fest: gerendert wird aus DB via loadMessages() in render().
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
     * Step 1 — Schnell. Photos ingesten, Pending-State setzen, Input leeren.
     * Triggert via JS den zweiten Request für den AI-Call.
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
                    source: 'onboarding-chat',
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
     * Step 2 — Langsam. Ruft den AI-Agent. RememberConversations persistiert.
     *
     * Wenn der Agent in diesem Turn FinalizeOnboarding aufgerufen hat (Vendor
     * Status → live), leiten wir direkt zum Dashboard weiter. Die Page-Component
     * redirected ebenfalls bei live, hier ist es nur die schnellere UX-Reaktion.
     */
    public function runAgent(): mixed
    {
        if ($this->queuedPrompt === '') {
            return null;
        }

        $prompt = $this->queuedPrompt;
        $this->queuedPrompt = '';

        $this->promptAgent($prompt);

        $this->pendingText = '';
        $this->pendingImageUrls = [];

        $this->dispatch('feed-updated');
        $this->dispatch('chat-scroll');

        $vendor = Vendor::find($this->vendorId);
        if ($vendor !== null && $vendor->status === 'live') {
            return $this->redirectRoute('dashboard', navigate: true);
        }

        return null;
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
            ."\nVision-Beschreibung: {$description}"
            ."\nAlt-Text: {$altText}";
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
