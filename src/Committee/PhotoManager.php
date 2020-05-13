<?php

namespace App\Committee;

use App\Entity\Committee;
use League\Flysystem\Filesystem;
use League\Glide\Server;

class PhotoManager
{
    private $storage;
    private $glide;

    public function __construct(Filesystem $storage, Server $glide)
    {
        $this->storage = $storage;
        $this->glide = $glide;
    }

    /**
     * Uploads and saves the ID photo of committee creator.
     */
    public function addPhotoFromCommand(CommitteeCommand $command, Committee $committee): void
    {
        if (null !== $photo = $command->getPhoto()) {
            $path = $committee->getPhotoPath();

            // Uploads the file : creates or updates if exists
            $this->storage->put($path, file_get_contents($command->getPhoto()->getPathname()));

            // Clears the cache file
            $this->glide->deleteCache($path);

            $committee->setPhotoUploaded(true);
        }
    }
}
