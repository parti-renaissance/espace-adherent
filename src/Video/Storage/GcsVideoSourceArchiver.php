<?php

declare(strict_types=1);

namespace App\Video\Storage;

use Google\Cloud\Storage\StorageClient;

class GcsVideoSourceArchiver implements VideoSourceArchiverInterface
{
    public function __construct(
        private readonly StorageClient $storage,
        private readonly string $gcloudBucket,
        private readonly string $scraperSourceBuckets,
    ) {
    }

    public function archive(string $sourceGcsUri, string $destinationPath): void
    {
        [$bucket, $object] = $this->parseGcsUri($sourceGcsUri);

        if (!\in_array($bucket, $this->allowedSourceBuckets(), true)) {
            throw new \InvalidArgumentException(\sprintf('Source bucket "%s" is not in the allowed scraper buckets.', $bucket));
        }

        $this->storage->bucket($bucket)->object($object)->rewrite($this->gcloudBucket, ['name' => $destinationPath]);
    }

    /**
     * @return array{0: string, 1: string} bucket, object path
     */
    private function parseGcsUri(string $uri): array
    {
        if (1 !== preg_match('#^gs://([^/]+)/(.+)$#', $uri, $matches)) {
            throw new \InvalidArgumentException(\sprintf('Invalid GCS URI "%s".', $uri));
        }

        return [$matches[1], $matches[2]];
    }

    /**
     * @return list<string>
     */
    private function allowedSourceBuckets(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->scraperSourceBuckets))));
    }
}
