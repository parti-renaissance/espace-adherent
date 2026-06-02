<?php

declare(strict_types=1);

namespace App\Video\Storage;

interface VideoSourceArchiverInterface
{
    public function archive(string $sourceGcsUri, string $destinationPath): void;
}
