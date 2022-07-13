<?php

namespace App\Storage;

use App\Storage\Exception\FileExistsException;
use App\Storage\Exception\FileNotFoundException;
use League\Flysystem\FileExistsException as LeagueFileExistsException;
use League\Flysystem\FileNotFoundException as LeagueFileNotFoundException;
use League\Flysystem\FilesystemInterface;

class Storage implements StorageInterface
{
    private FilesystemInterface $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function has(string $path): bool
    {
        return $this->filesystem->has($path);
    }

    public function getMimetype(string $path): string
    {
        try {
            return $this->filesystem->getMimetype($path);
        } catch (LeagueFileNotFoundException $exception) {
            throw $this->createFileNotFoundException($path);
        }
    }

    public function read(string $path): string
    {
        try {
            return $this->filesystem->read($path);
        } catch (LeagueFileNotFoundException $exception) {
            throw $this->createFileNotFoundException($path);
        }
    }

    public function readStream(string $path)
    {
        try {
            return $this->filesystem->readStream($path);
        } catch (LeagueFileNotFoundException $exception) {
            throw $this->createFileNotFoundException($path);
        }
    }

    public function put(string $path, string $contents, array $config = []): bool
    {
        return $this->filesystem->put($path, $contents, $config);
    }

    public function copy(string $path, string $newPath): bool
    {
        try {
            return $this->filesystem->copy($path, $newPath);
        } catch (LeagueFileNotFoundException $exception) {
            throw $this->createFileNotFoundException($path);
        } catch (LeagueFileExistsException $exception) {
            throw $this->createFileExistsException($newPath);
        }
    }

    public function delete(string $path): bool
    {
        try {
            return $this->filesystem->delete($path);
        } catch (LeagueFileNotFoundException $exception) {
            throw $this->createFileNotFoundException($path);
        }
    }

    public function listContents(string $directory, bool $recursive = false): array
    {
        return $this->filesystem->listContents($directory, $recursive);
    }

    private function createFileNotFoundException(string $path): FileNotFoundException
    {
        return new FileNotFoundException($path);
    }

    private function createFileExistsException(string $path): FileExistsException
    {
        return new FileExistsException($path);
    }
}
