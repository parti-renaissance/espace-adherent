<?php

namespace AppBundle\Documents;

use League\Flysystem\Filesystem;

class DocumentRepository
{
    public const DIRECTORY_ROOT = 'documents';
    public const DIRECTORY_ADHERENTS = 'adherents';
    public const DIRECTORY_HOSTS = 'animateurs';
    public const DIRECTORY_FOREIGN_HOSTS = 'animateurs-etrangers';
    public const DIRECTORY_REFERENTS = 'referents';

    private $storage;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
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
            'mimetype' => $this->storage->getMimetype($path),
            'content' => $this->storage->read($path),
        ];
    }

    /**
     * @return Document[]
     */
    private function map(string $pathPrefix, array $files): array
    {
        $pathPrefixLength = \strlen($pathPrefix);
        $directory = [];

        foreach ($files as $file) {
            $directory[] = new Document(
                $file['type'],
                $file['filename'],
                ('file' === $file['type'] && \array_key_exists('extension', $file) && $file['extension']) ? $file['extension'] : '',
                ltrim(substr($file['path'], $pathPrefixLength), '/')
            );
        }

        return $directory;
    }
}
