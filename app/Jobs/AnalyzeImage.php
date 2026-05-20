<?php

namespace App\Jobs;

use App\Ai\Agents\ImageAnalyzer;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Files\Image as AiImage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Analyzes a freshly uploaded vendor image via the vision agent and
 * writes the result to the media row's `custom_properties`.
 *
 * Dispatched by VendorImageIngestor. Default is sync (see
 * Ingestor::ingest…(analyzeSync: true)), but can run async when the
 * caller does not need to wait for the description.
 *
 * Writes:
 *  - description, alt_text, suggested_intent, tags, detected_text, locale_hint
 *  - analyzed_at (timestamp)
 *  - Renames the file with a descriptive slug so the image is easy to
 *    identify in the Spatie media library.
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
            // Already analyzed — no double job re-runs.
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

        // Rename file with a descriptive slug — the UUID variant is
        // unintelligible for AI/vendor. Keep the original extension.
        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION) ?: 'jpg';
        $intent = $payload['suggested_intent'];
        $slug = Str::slug(Str::limit($payload['description'], 40, ''));
        $slug = $slug !== '' ? $slug : 'image';

        $media->name = trim($payload['alt_text']) !== '' ? $payload['alt_text'] : $media->name;

        $originalFileName = $media->file_name;
        $media->file_name = sprintf('%s-%s-%d.%s', $intent, $slug, $media->id, $extension);
        $media->save();

        // Spatie's MediaObserver renames the file on disk via syncFileNames(),
        // but Storage::move() returns false silently if the source is missing
        // or the disk has a glitch — leaving DB pointing at a non-existent
        // file. Verify the new path resolves, and roll back the DB if not.
        $disk = Storage::disk($media->disk);
        $newPath = "{$media->id}/{$media->file_name}";

        if (! $disk->exists($newPath)) {
            Log::warning('AnalyzeImage rename did not produce file on disk; reverting', [
                'media_id' => $media->id,
                'expected' => $newPath,
                'original' => $originalFileName,
            ]);
            $media->file_name = $originalFileName;
            $media->saveQuietly();
        }
    }
}
