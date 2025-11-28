<?php

declare(strict_types=1);

namespace App\Documents;

use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;

class DocumentRepository
{
    public const DIRECTORY_ROOT = 'documents';
    public const DIRECTORY_ADHERENTS = 'adherents';
    public const DIRECTORY_HOSTS = 'animateurs';
    public const DIRECTORY_FOREIGN_HOSTS = 'animateurs-etrangers';
    public const DIRECTORY_REFERENTS = 'referents';

    private $storage;

    public function __construct(FilesystemOperator $defaultStorage)
    {
        $this->storage = $defaultStorage;
    }

    /**
     * @return Document[]
     */
    public function listAdherentDirectory(string $path = '/'): array
    {
        return $this->listDirectory(self::DIRECTORY_ADHERENTS, $path);
    }

    /**
     * @return Document[]
     */
    public function listHostDirectory(string $path = '/'): array
    {
        return $this->listDirectory(self::DIRECTORY_HOSTS, $path);
    }

    /**
     * @return Document[]
     */
    public function listForeignHostDirectory(string $path = '/'): array
    {
        return $this->listDirectory(self::DIRECTORY_FOREIGN_HOSTS, $path);
    }

    /**
     * @return Document[]
     */
    public function listReferentDirectory(string $path = '/'): array
    {
        return $this->listDirectory(self::DIRECTORY_REFERENTS, $path);
    }

    /**
     * @return Document[]
     */
    public function listDirectory(string $type, string $path): array
    {
        return $this->map(
            self::DIRECTORY_ROOT.'/'.$type,
            $this->storage->listContents(self::DIRECTORY_ROOT.'/'.$type.'/'.$path)
        );
    }

    public function readDocument(string $type, string $path): array
    {
        $path = self::DIRECTORY_ROOT.'/'.$type.'/'.$path;

        return [
            'mimetype' => $this->storage->mimeType($path),
            'content' => $this->storage->read($path),
        ];
    }

    /**
     * @return Document[]
     */
    private function map(string $pathPrefix, DirectoryListing $files): array
    {
        $pathPrefixLength = \strlen($pathPrefix);
        $directory = [];

        /** @var StorageAttributes $file */
        foreach ($files as $file) {
            $metadata = pathinfo($file->path());
            $directory[] = new Document(
                $file->type(),
                $metadata['filename'],
                ($file->isFile() && \array_key_exists('extension', $metadata) && $metadata['extension']) ? $metadata['extension'] : '',
                ltrim(substr($file->path(), $pathPrefixLength), '/')
            );
        }

        return $directory;
    }
}
