<?php

namespace App\Storage;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FilesystemAdapterFactory
{
    public static function createAdapter(
        string $environment,
        string $localPath,
        $gcloudId,
        $gcloudKeyFilePath,
        $gcloudBucket,
        UrlGeneratorInterface $urlGenerator
    ) {
        if ('prod' !== $environment) {
            $adapter = new WebLocalAdapter($localPath);
            $adapter->setUrlGenerator($urlGenerator);

            return $adapter;
        }

        $storage = new StorageClient([
            'projectId' => $gcloudId,
            'keyFilePath' => $gcloudKeyFilePath,
        ]);

        return new CachedAdapter(
            new GoogleStorageAdapter($storage, $storage->bucket($gcloudBucket)),
            new Memory()
        );
    }
}
