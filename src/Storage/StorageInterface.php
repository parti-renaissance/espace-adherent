<?php

namespace App\Storage;

use App\Storage\Exception\FileExistsException;
use App\Storage\Exception\FileNotFoundException;

interface StorageInterface
{
    public function has(string $path): bool;

    /**
     * @throws FileNotFoundException
     */
    public function getMimetype(string $path): string;

    /**
     * @throws FileNotFoundException Thrown if $path does not exist
     */
    public function read(string $path): string;

    /**
     * @throws FileNotFoundException Thrown if $path does not exist
     *
     * @return resource
     */
    public function readStream(string $path);

    public function put(string $path, string $contents, array $config = []): bool;

    /**
     * @throws FileExistsException   Thrown if $newpath exists
     * @throws FileNotFoundException Thrown if $path does not exist
     */
    public function copy(string $path, string $newpath): bool;

    /**
     * @throws FileNotFoundException
     */
    public function delete(string $path): bool;

    public function listContents(string $directory, bool $recursive = false): array;
}
