<?php

namespace App\Services;

use App\Jobs\AnalyzeImage;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Central entry point for every image upload onto a vendor.
 *
 * Regardless of where the image comes from — onboarding chat, dashboard
 * drawer, possibly the contact form or admin moderation in the future, or
 * from the AI agent via the `UploadImage` tool — every path lands here.
 * This way there is only ONE place that:
 *
 *  - attaches the file to the vendor via Spatie Media Library
 *  - enriches the media row with `intent` + `source` custom properties
 *  - dispatches the AnalyzeImage job (sync by default — the caller usually
 *    waits for the description to pass it to the AI agent)
 *  - returns the completed media row with description, alt text,
 *    suggested intent, etc.
 *
 * Supported inputs: TemporaryUploadedFile / UploadedFile / local path /
 * base64 string + mime / external URL. All converge on `ingestPath()`.
 */
class VendorImageIngestor
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Image from a Livewire upload or a regular Laravel UploadedFile.
     */
    public function fromUpload(
        Vendor $vendor,
        TemporaryUploadedFile|UploadedFile $upload,
        ?string $intent = null,
        string $source = 'upload',
        bool $analyzeSync = true,
    ): Media {
        $this->assertMime($upload->getMimeType() ?? '');

        return $this->ingestPath(
            vendor: $vendor,
            absolutePath: $upload->getRealPath(),
            originalName: $upload->getClientOriginalName() ?: ('upload.'.($upload->guessClientExtension() ?: 'jpg')),
            intent: $intent,
            source: $source,
            analyzeSync: $analyzeSync,
            moveOriginal: false,
        );
    }

    /**
     * Image from a local path.
     */
    public function fromPath(
        Vendor $vendor,
        string $path,
        ?string $intent = null,
        string $source = 'path',
        bool $analyzeSync = true,
    ): Media {
        if (! is_file($path)) {
            throw new InvalidArgumentException("File does not exist: {$path}");
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';
        $this->assertMime($mime);

        return $this->ingestPath(
            vendor: $vendor,
            absolutePath: $path,
            originalName: basename($path),
            intent: $intent,
            source: $source,
            analyzeSync: $analyzeSync,
            moveOriginal: false,
        );
    }

    /**
     * Image from a base64-encoded string (typical when the AI agent passes
     * an image through directly).
     */
    public function fromBase64(
        Vendor $vendor,
        string $base64,
        string $mime,
        ?string $intent = null,
        string $source = 'base64',
        bool $analyzeSync = true,
    ): Media {
        $this->assertMime($mime);

        $binary = base64_decode($base64, strict: true);

        if ($binary === false) {
            throw new InvalidArgumentException('data must be a valid base64-encoded string.');
        }

        $extension = $this->extensionFromMime($mime);
        $tempPath = tempnam(sys_get_temp_dir(), 'feedai-img-').'.'.$extension;
        file_put_contents($tempPath, $binary);

        return $this->ingestPath(
            vendor: $vendor,
            absolutePath: $tempPath,
            originalName: 'upload.'.$extension,
            intent: $intent,
            source: $source,
            analyzeSync: $analyzeSync,
            moveOriginal: true,
        );
    }

    /**
     * Image from a publicly accessible URL.
     */
    public function fromUrl(
        Vendor $vendor,
        string $url,
        ?string $intent = null,
        string $source = 'url',
        bool $analyzeSync = true,
    ): Media {
        $binary = @file_get_contents($url);

        if ($binary === false) {
            throw new InvalidArgumentException("Could not fetch image from URL: {$url}");
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'feedai-img-');
        file_put_contents($tempPath, $binary);

        $mime = mime_content_type($tempPath) ?: 'image/jpeg';
        $this->assertMime($mime);

        $extension = $this->extensionFromMime($mime);
        $finalPath = $tempPath.'.'.$extension;
        rename($tempPath, $finalPath);

        return $this->ingestPath(
            vendor: $vendor,
            absolutePath: $finalPath,
            originalName: basename(parse_url($url, PHP_URL_PATH) ?? 'remote.'.$extension),
            intent: $intent,
            source: $source,
            analyzeSync: $analyzeSync,
            moveOriginal: true,
        );
    }

    /**
     * Shared endpoint. Writes to Spatie, dispatches analysis,
     * returns the completed media row.
     */
    private function ingestPath(
        Vendor $vendor,
        string $absolutePath,
        string $originalName,
        ?string $intent,
        string $source,
        bool $analyzeSync,
        bool $moveOriginal,
    ): Media {
        $prelimSlug = Str::slug($intent ?: 'image') ?: 'image';
        $preliminaryFilename = sprintf(
            '%s-%s.%s',
            $prelimSlug,
            Str::lower(Str::random(8)),
            pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg',
        );

        $adder = $vendor
            ->addMedia($absolutePath)
            ->usingFileName($preliminaryFilename)
            ->usingName($intent ?: 'Upload')
            ->withCustomProperties([
                'intent' => $intent,
                'source' => $source,
                'uploaded_at' => now()->toIso8601String(),
                'original_name' => $originalName,
            ]);

        if (! $moveOriginal) {
            $adder->preservingOriginal();
        }

        $media = $adder->toMediaCollection('images');

        if ($analyzeSync) {
            (new AnalyzeImage($media->id))->handle();

            return $media->refresh();
        }

        AnalyzeImage::dispatch($media->id);

        return $media;
    }

    private function assertMime(string $mime): void
    {
        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new InvalidArgumentException("Unsupported mime type: {$mime}. Allowed: ".implode(', ', self::ALLOWED_MIMES));
        }
    }

    private function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new InvalidArgumentException("Unsupported mime type: {$mime}"),
        };
    }
}
