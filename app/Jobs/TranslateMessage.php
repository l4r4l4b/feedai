<?php

namespace App\Jobs;

use App\Ai\Agents\MessageTranslator;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Übersetzt eine Chat-Nachricht in die jeweils andere Sprache und
 * schreibt das Ergebnis nach `translated_text` + `translated_locale`.
 *
 * Wird automatisch bei jedem Message::create dispatched. Bei Source==Target
 * (z.B. Vendor und Tourist sprechen Englisch) ist es ein No-Op.
 */
class TranslateMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $messageId) {}

    public function handle(): void
    {
        $message = Message::with('conversation.vendor')->find($this->messageId);

        if (! $message) {
            return;
        }

        if ($message->translated_text !== null) {
            // Already translated — nothing to do.
            return;
        }

        $vendor = $message->conversation?->vendor;

        if ($vendor === null) {
            return;
        }

        $vendorLocale = $vendor->locale ?? 'th';
        $touristLocale = $message->conversation->tourist_locale;

        $target = $message->sender === 'tourist' ? $vendorLocale : $touristLocale;
        $source = $message->original_locale;

        if ($source === $target) {
            $message->forceFill([
                'translated_text' => $message->original_text,
                'translated_locale' => $target,
            ])->save();

            return;
        }

        try {
            $response = (new MessageTranslator($source, $target))->prompt($message->original_text);
            $translated = trim((string) $response);
        } catch (\Throwable $e) {
            Log::warning('Message translation failed', [
                'message_id' => $message->id,
                'source' => $source,
                'target' => $target,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $message->forceFill([
            'translated_text' => $translated,
            'translated_locale' => $target,
        ])->save();
    }
}
