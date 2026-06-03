<?php

declare(strict_types=1);

namespace App\SocialNetwork\Image\Storage;

use App\Storage\Gcs\ScraperGcsSourceParser;
use Google\Cloud\Storage\StorageClient;
use League\Flysystem\FilesystemOperator;

class FlysystemFeedImagePublisher implements FeedImagePublisherInterface
{
    private const int MAX_BYTES = 15 * 1024 * 1024;
    private const string DEFAULT_EXTENSION = 'jpg';
    private const string PATH_PREFIX = 'social-feed/';

    /**
     * Raster types we accept. SVG is intentionally excluded: it is an active-content format
     * (embedded scripts) that would enable stored XSS when served from the media CDN domain.
     */
    private const array ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const array ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct(
        private readonly ScraperGcsSourceParser $sourceParser,
        private readonly StorageClient $storage,
        private readonly FilesystemOperator $mediaStorage,
    ) {
    }

    public function expectedPath(string $sourceGcsUri): string
    {
        return self::PATH_PREFIX.sha1($sourceGcsUri).'.'.$this->extension($sourceGcsUri);
    }

    public function publish(string $sourceGcsUri): PublishedImage
    {
        [$bucket, $object] = $this->sourceParser->parse($sourceGcsUri);

        $path = $this->expectedPath($sourceGcsUri);

        if ($this->mediaStorage->fileExists($path)) {
            $dimensions = getimagesizefromstring($this->mediaStorage->read($path)) ?: [];

            return new PublishedImage($path, $dimensions[0] ?? null, $dimensions[1] ?? null);
        }

        $gcsObject = $this->storage->bucket($bucket)->object($object);

        // Reject on the object size metadata BEFORE pulling it into memory (DoS / OOM protection).
        if ((int) ($gcsObject->info()['size'] ?? 0) > self::MAX_BYTES) {
            throw new \InvalidArgumentException(\sprintf('Source object exceeds the maximum size of %d bytes.', self::MAX_BYTES));
        }

        $content = $gcsObject->downloadAsString();

        $this->assertIsAllowedImage($content);

        $this->mediaStorage->write($path, $content);

        $dimensions = getimagesizefromstring($content) ?: [];

        return new PublishedImage($path, $dimensions[0] ?? null, $dimensions[1] ?? null);
    }

    private function assertIsAllowedImage(string $content): void
    {
        // Belt-and-suspenders: the metadata guard above may be missing a size; re-check the payload.
        if (\strlen($content) > self::MAX_BYTES) {
            throw new \InvalidArgumentException(\sprintf('Image exceeds the maximum size of %d bytes.', self::MAX_BYTES));
        }

        $mimeType = new \finfo(\FILEINFO_MIME_TYPE)->buffer($content) ?: '';

        // Whitelist raster types only — never SVG or any active-content format (stored XSS risk).
        if (!\in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException(\sprintf('Source content is not an allowed image type (detected "%s").', $mimeType));
        }
    }

    private function extension(string $sourceGcsUri): string
    {
        $extension = strtolower(pathinfo(parse_url($sourceGcsUri, \PHP_URL_PATH) ?? '', \PATHINFO_EXTENSION));

        // Never let the source URL dictate an unsafe served extension (e.g. .svg/.html).
        return \in_array($extension, self::ALLOWED_EXTENSIONS, true) ? $extension : self::DEFAULT_EXTENSION;
    }
}
