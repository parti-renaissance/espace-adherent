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

    public function __construct(
        private readonly ScraperGcsSourceParser $sourceParser,
        private readonly StorageClient $storage,
        private readonly FilesystemOperator $publicUserFileStorage,
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

        if ($this->publicUserFileStorage->fileExists($path)) {
            return new PublishedImage($path);
        }

        $content = $this->storage->bucket($bucket)->object($object)->downloadAsString();

        $this->assertIsImage($content);

        $this->publicUserFileStorage->write($path, $content);

        $dimensions = getimagesizefromstring($content) ?: [];

        return new PublishedImage($path, $dimensions[0] ?? null, $dimensions[1] ?? null);
    }

    private function assertIsImage(string $content): void
    {
        if (\strlen($content) > self::MAX_BYTES) {
            throw new \InvalidArgumentException(\sprintf('Image exceeds the maximum size of %d bytes.', self::MAX_BYTES));
        }

        $mimeType = new \finfo(\FILEINFO_MIME_TYPE)->buffer($content) ?: '';

        if (!str_starts_with($mimeType, 'image/')) {
            throw new \InvalidArgumentException(\sprintf('Source content is not an image (detected "%s").', $mimeType));
        }
    }

    private function extension(string $sourceGcsUri): string
    {
        $extension = strtolower(pathinfo(parse_url($sourceGcsUri, \PHP_URL_PATH) ?? '', \PATHINFO_EXTENSION));

        return '' !== $extension ? $extension : self::DEFAULT_EXTENSION;
    }
}
