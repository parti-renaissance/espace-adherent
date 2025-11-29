<?php

declare(strict_types=1);

namespace App\Storage;

use League\Flysystem\FilesystemOperator;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageStorage
{
    /**
     * @var FilesystemOperator
     */
    private $storage;

    /**
     * @var Server
     */
    private $glide;

    public function __construct(FilesystemOperator $defaultStorage, Server $glide)
    {
        $this->storage = $defaultStorage;
        $this->glide = $glide;
    }

    public function save(UploadedFile $file, string $path, ?string $oldPath = null): void
    {
        // Clears the old file if needed
        if (null !== $oldPath && $this->storage->has($oldPath)) {
            $this->storage->delete($oldPath);
        }

        // Uploads the file : creates or updates if exists
        $this->storage->write($path, file_get_contents($file->getPathname()));

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
