<?php

namespace App\Jobs;

use App\Ai\Agents\ImageAnalyzer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Files\Image as AiImage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Analysiert ein eben hochgeladenes Vendor-Bild via Vision-Agent und
 * schreibt das Ergebnis in `custom_properties` der Media-Row.
 *
 * Wird vom VendorImageIngestor dispatched. Standard ist sync (siehe
 * Ingestor::ingest…(analyzeSync: true)), kann aber async laufen wenn der
 * Caller nicht auf die Beschreibung warten muss.
 *
 * Schreibt:
 *  - description, alt_text, suggested_intent, tags, detected_text, locale_hint
 *  - analyzed_at (Timestamp)
 *  - Erneuert den File-Namen mit beschreibendem Slug, damit das Bild in
 *    der Spatie-Library leicht zu identifizieren ist.
 */
class AnalyzeImage implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $mediaId) {}

    public function uniqueId(): string
    {
        return "analyze-image:{$this->mediaId}";
    }

    public function uniqueFor(): int
    {
        return 120;
    }

    public function handle(): void
    {
        $media = Media::find($this->mediaId);

        if (! $media) {
            return;
        }

        if (! empty($media->getCustomProperty('analyzed_at'))) {
            // Bereits analysiert — Job-Re-Run gibt's nicht zweimal.
            return;
        }

        try {
            $analysis = (new ImageAnalyzer)->prompt(
                'Analyze this vendor image.',
                attachments: [AiImage::fromPath($media->getPath())],
            );

            $payload = [
                'description' => (string) ($analysis['description'] ?? ''),
                'alt_text' => (string) ($analysis['alt_text'] ?? ''),
                'suggested_intent' => (string) ($analysis['suggested_intent'] ?? 'other'),
                'tags' => array_values(array_filter((array) ($analysis['tags'] ?? []))),
                'detected_text' => (string) ($analysis['detected_text'] ?? ''),
                'locale_hint' => (string) ($analysis['locale_hint'] ?? ''),
                'analyzed_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            Log::warning('AnalyzeImage failed', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);

            $media->setCustomProperty('analyzed_at', now()->toIso8601String());
            $media->setCustomProperty('analysis_error', $e->getMessage());
            $media->save();

            return;
        }

        foreach ($payload as $key => $value) {
            $media->setCustomProperty($key, $value);
        }

        // Datei umbenennen mit beschreibendem Slug — die UUID-Variante
        // ist für AI/Vendor unverständlich. Original-Extension behalten.
        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION) ?: 'jpg';
        $intent = $payload['suggested_intent'];
        $slug = Str::slug(Str::limit($payload['description'], 40, ''));
        $slug = $slug !== '' ? $slug : 'image';

        $media->name = trim($payload['alt_text']) !== '' ? $payload['alt_text'] : $media->name;
        $media->file_name = sprintf('%s-%s-%d.%s', $intent, $slug, $media->id, $extension);
        $media->save();
    }
}
