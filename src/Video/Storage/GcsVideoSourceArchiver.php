<?php

declare(strict_types=1);

namespace App\Video\Storage;

use App\Storage\Gcs\ScraperGcsSourceParser;
use Google\Cloud\Storage\StorageClient;

class GcsVideoSourceArchiver implements VideoSourceArchiverInterface
{
    public function __construct(
        private readonly StorageClient $storage,
        private readonly ScraperGcsSourceParser $sourceParser,
        private readonly string $gcloudBucket,
    ) {
    }

    public function archive(string $sourceGcsUri, string $destinationPath): void
    {
        [$bucket, $object] = $this->sourceParser->parse($sourceGcsUri);

        $this->storage->bucket($bucket)->object($object)->rewrite($this->gcloudBucket, ['name' => $destinationPath]);
    }
}
