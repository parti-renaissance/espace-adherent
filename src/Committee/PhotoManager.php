<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use Doctrine\Common\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoManager
{
    private $registry;
    private $storage;
    private $glide;

    public function __construct(ManagerRegistry $registry, Filesystem $storage, Server $glide)
    {
        $this->registry = $registry;
        $this->storage = $storage;
        $this->glide = $glide;
    }

    /**
     * Uploads and saves the ID photo of committee creator.
     *
     * @param Committee $committee
     */
    public function addPhotoFromCommand(CommitteeCommand $command, Committee $committee): void
    {
        if (null !== $photo = $command->getPhoto()) {
            if (!$photo instanceof UploadedFile) {
                throw new \RuntimeException(sprintf('The photo must be an instance of %s', UploadedFile::class));
            }

            $path = $committee->getPhotoPath();

            // Uploads the file : creates or updates if exists
            $this->storage->put($path, file_get_contents($command->getPhoto()->getPathname()));

            // Clears the cache file
            $this->glide->deleteCache($path);

            $committee->setPhotoUploaded(true);
        }
    }
}
