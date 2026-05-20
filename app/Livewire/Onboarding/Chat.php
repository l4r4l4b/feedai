<?php

namespace App\Livewire\Onboarding;

use App\Ai\Agents\OnboardingAgent;
use App\Models\OnboardingSession;
use App\Models\User;
use App\Models\Vendor;
use App\Services\VendorImageIngestor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Onboarding-Chat. Sendet User-Messages an den OnboardingAgent und rendert
 * Agent-Responses + Tool-Call-Bestätigungen. Persistiert Conversation-State
 * via OnboardingAgent's RemembersConversations-Trait.
 *
 * Bild-Uploads laufen durch den VendorImageIngestor — Single Source of Truth
 * für alle Vendor-Bilder-Eingänge. Das ingestor-Output (URL + AI-Description)
 * wird in die Chat-Nachricht eingebettet, damit der OnboardingAgent versteht
 * was hochgeladen wurde, ohne dass er das Bild selbst sehen muss.
 *
 * Nach jedem Agent-Turn wird ein Browser-Event "feed-updated" dispatched,
 * der die eingebettete Live-Preview neu lädt.
 */
#[Title('Onboarding')]
class Chat extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $vendorId;

    #[Locked]
    public ?string $conversationId = null;

    /** @var array<int, array{role:string, text:string}> */
    public array $messages = [];

    public string $draft = '';

    public ?TemporaryUploadedFile $photo = null;

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $vendor = $user->vendor;
        abort_unless($vendor instanceof Vendor, 404);

        $this->vendorId = $vendor->id;
        $this->conversationId = $this->resolveConversationId($vendor);

        if ($this->messages === []) {
            $this->sendInitialGreeting();
        }
    }

    public function sendMessage(VendorImageIngestor $ingestor): void
    {
        $text = trim($this->draft);
        $photo = $this->photo;

        if ($text === '' && $photo === null) {
            return;
        }

        $userMessage = $text;
        $agentPrompt = $text;

        if ($photo !== null) {
            $media = $this->ingestPhoto($ingestor, $photo);
            $this->photo = null;

            $userMessage = $this->buildUserMessageWithPhoto($text, $media);
            $agentPrompt = $this->buildAgentPromptWithPhoto($text, $media);
        }

        $this->draft = '';
        $this->messages[] = ['role' => 'user', 'text' => $userMessage];

        $response = $this->promptAgent($agentPrompt);

        $this->messages[] = ['role' => 'assistant', 'text' => $response];

        $this->dispatch('feed-updated');
    }

    public function render(): View
    {
        return view('livewire.onboarding.chat');
    }

    private function resolveConversationId(Vendor $vendor): ?string
    {
        return OnboardingSession::where('vendor_id', $vendor->id)
            ->where('status', 'in_progress')
            ->latest()
            ->value('conversation_id');
    }

    private function sendInitialGreeting(): void
    {
        $vendor = Vendor::findOrFail($this->vendorId);
        $greeting = "Hi {$vendor->name}! Schön, dass Du da bist. "
            .'Erzähl mir kurz: Was machst Du und wo bist Du?';

        $this->messages[] = ['role' => 'assistant', 'text' => $greeting];
        $this->dispatch('feed-updated');
    }

    private function ingestPhoto(VendorImageIngestor $ingestor, TemporaryUploadedFile $photo): Media
    {
        $vendor = Vendor::findOrFail($this->vendorId);

        return $ingestor->fromUpload(
            vendor: $vendor,
            upload: $photo,
            intent: null,
            source: 'onboarding-chat',
            analyzeSync: true,
        );
    }

    /**
     * User-sichtbare Repräsentation der eigenen Nachricht inkl. Bild-Vorschau-Info.
     */
    private function buildUserMessageWithPhoto(string $text, Media $media): string
    {
        $description = (string) $media->getCustomProperty('description', '');
        $caption = $description !== '' ? "📷 {$description}" : '📷 Bild gesendet';

        return $text === '' ? $caption : $text."\n".$caption;
    }

    /**
     * Agent-Prompt mit struktureller Bild-Info, damit der Agent fundiert
     * weiterarbeiten kann ohne das Bild selbst sehen zu müssen.
     */
    private function buildAgentPromptWithPhoto(string $text, Media $media): string
    {
        $url = $media->getUrl();
        $description = (string) $media->getCustomProperty('description', '');
        $altText = (string) $media->getCustomProperty('alt_text', '');
        $suggestedIntent = (string) $media->getCustomProperty('suggested_intent', 'other');
        $tags = (array) $media->getCustomProperty('tags', []);
        $tagsLine = $tags === [] ? '' : ' tags='.implode(',', $tags);

        $block = "[IMAGE_UPLOADED url={$url} suggested_intent={$suggestedIntent}{$tagsLine}]"
            ."\nVision-Beschreibung: {$description}"
            ."\nAlt-Text: {$altText}";

        return $text === '' ? $block : $text."\n\n".$block;
    }

    private function promptAgent(string $message): string
    {
        $vendor = Vendor::findOrFail($this->vendorId);
        /** @var User $user */
        $user = Auth::user();

        $agent = $this->conversationId
            ? (new OnboardingAgent($vendor))->continue($this->conversationId, as: $user)
            : (new OnboardingAgent($vendor))->forUser($user);

        $response = $agent->prompt($message);

        if (! $this->conversationId && property_exists($response, 'conversationId')) {
            $this->conversationId = $response->conversationId;
            $this->persistConversationId();
        }

        return (string) $response;
    }

    private function persistConversationId(): void
    {
        if ($this->conversationId === null) {
            return;
        }

        OnboardingSession::where('vendor_id', $this->vendorId)
            ->where('status', 'in_progress')
            ->update(['conversation_id' => $this->conversationId]);
    }
}
