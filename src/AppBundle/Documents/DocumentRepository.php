<?php

namespace AppBundle\Documents;

use League\Flysystem\Filesystem;

class DocumentRepository
{
    const DIRECTORY_ROOT = 'documents';
    const DIRECTORY_ADHERENTS = 'adherents';
    const DIRECTORY_HOSTS = 'animateurs';
    const DIRECTORY_REFERENTS = 'referents';

    private $storage;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param string $path
     *
     * @return Document[]
     */
    public function listAdherentDirectory(string $path = '/'): array
    {
        return $this->listDirectory(self::DIRECTORY_ADHERENTS, $path);
    }

    /**
     * @param string $path
     *
     * @return Document[]
     */
    public function listHostDirectory(string $path = '/'): array
    {
        return $this->listDirectory(self::DIRECTORY_HOSTS, $path);
    }

    /**
     * @param string $path
     *
     * @return Document[]
     */
    public function listReferentDirectory(string $path = '/'): array
    {
        return $this->listDirectory(self::DIRECTORY_REFERENTS, $path);
    }

    /**
     * @param string $type
     * @param string $path
     *
     * @return Document[]
     */
    public function listDirectory(string $type, string $path): array
    {
        return $this->map(
            self::DIRECTORY_ROOT.'/'.$type,
            $this->storage->listContents(self::DIRECTORY_ROOT.'/'.$type.'/'.$path)
        );
    }

    /**
     * @return array
     */
    public function readDocument(string $type, string $path): array
    {
        $path = self::DIRECTORY_ROOT.'/'.$type.'/'.$path;

        return [
            'mimetype' => $this->storage->getMimetype($path),
            'content' => $this->storage->read($path),
        ];
    }

    /**
     * @param array $files
     *
     * @return Document[]
     */
    private function map(string $pathPrefix, array $files): array
    {
        $pathPrefixLength = strlen($pathPrefix);
        $directory = [];

        foreach ($files as $file) {
            $directory[] = new Document(
                $file['type'],
                $file['filename'],
                ('file' === $file['type']) ? $file['extension'] : '',
                ltrim(substr($file['path'], $pathPrefixLength), '/')
            );
        }

        return $directory;
    }
}
