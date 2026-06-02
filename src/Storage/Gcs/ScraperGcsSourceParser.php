<?php

declare(strict_types=1);

namespace App\Storage\Gcs;

/**
 * Parses a `gs://bucket/object` scraper source URI and enforces the bucket allowlist.
 *
 * This is the single security boundary guarding which buckets may be read from before any object
 * is copied or published. Shared by the video archiver and the feed image publisher.
 */
class ScraperGcsSourceParser
{
    public function __construct(private readonly string $scraperSourceBuckets)
    {
    }

    /**
     * @return array{0: string, 1: string} bucket, object path
     *
     * @throws \InvalidArgumentException when the URI is malformed or the bucket is not allowed
     */
    public function parse(string $gcsUri): array
    {
        if (1 !== preg_match('#^gs://([^/]+)/(.+)$#', $gcsUri, $matches)) {
            throw new \InvalidArgumentException(\sprintf('Invalid GCS URI "%s".', $gcsUri));
        }

        [, $bucket, $object] = $matches;

        if (!\in_array($bucket, $this->allowedSourceBuckets(), true)) {
            throw new \InvalidArgumentException(\sprintf('Source bucket "%s" is not in the allowed scraper buckets.', $bucket));
        }

        return [$bucket, $object];
    }

    /**
     * @return list<string>
     */
    private function allowedSourceBuckets(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->scraperSourceBuckets))));
    }
}
