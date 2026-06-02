<?php

declare(strict_types=1);

namespace Tests\App\Test\Video;

use App\Video\Storage\VideoSourceArchiverInterface;

/**
 * Dev/test archiver: does not touch GCS.
 */
class NoOpVideoSourceArchiver implements VideoSourceArchiverInterface
{
    public function archive(string $sourceGcsUri, string $destinationPath): void
    {
        // No-op.
    }
}
