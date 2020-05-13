<?php

namespace App\EntityListener;

use App\Entity\EntityFileInterface;
use Doctrine\ORM\Mapping as ORM;
use League\Flysystem\Filesystem;
use League\Glide\Server;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileListener
{
    private $storage;
    private $glide;

    public function __construct(Filesystem $storage, Server $glide)
    {
        $this->storage = $storage;
        $this->glide = $glide;
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function processFile(EntityFileInterface $entityFile): void
    {
        if ($entityFile->getFile()) {
            if (!($file = $entityFile->getFile()) instanceof UploadedFile) {
                throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
            }

            // Clears the old file if needed
            if ($oldPath = $entityFile->getPath()) {
                $this->storage->delete($oldPath);
            }

            $entityFile->setExtension($extension = $file->getClientOriginalExtension());
            $entityFile->setPath($path = sprintf(
                '%s/%s.%s',
                $entityFile->getPrefixPath(),
                Uuid::uuid4()->toString(),
                $extension
            ));

            // Uploads the file : creates or updates if exists
            $this->storage->put($path, file_get_contents($file->getPathname()));

            $this->glide->deleteCache($path);

            $entityFile->setFile(null);
        }
    }
}
