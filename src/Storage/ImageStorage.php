<?php

namespace App\Storage;

use League\Flysystem\FilesystemInterface;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageStorage
{
    /**
     * @var FilesystemInterface
     */
    private $storage;

    /**
     * @var Server
     */
    private $glide;

    public function __construct(FilesystemInterface $storage, Server $glide)
    {
        $this->storage = $storage;
        $this->glide = $glide;
    }

    public function save(UploadedFile $file, string $path, ?string $oldPath = null): void
    {
        // Clears the old file if needed
        if (null !== $oldPath && $this->storage->has($oldPath)) {
            $this->storage->delete($oldPath);
        }

        // Uploads the file : creates or updates if exists
        $this->storage->put($path, file_get_contents($file->getPathname()));

        // Clears the cache file
        $this->glide->deleteCache($path);
    }

    public function remove(string $path): void
    {
        // Deletes the file
        $this->storage->delete($path);

        // Clears the cache file
        $this->glide->deleteCache($path);
    }

    public function has(string $path): bool
    {
        return $this->storage->has($path);
    }
}
