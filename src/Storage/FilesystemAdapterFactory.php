<?php

namespace AppBundle\Storage;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

class FilesystemAdapterFactory
{
    public static function createAdapter(
        string $environment,
        string $localPath,
        $gcloudId,
        $gcloudKeyFilePath,
        $gcloudBucket,
        string $pathPrefix = null
    ) {
        if ('prod' !== $environment) {
            return new Local($localPath.'/'.$pathPrefix);
        }

        $storage = new StorageClient([
            'projectId' => $gcloudId,
            'keyFilePath' => $gcloudKeyFilePath,
        ]);

        return new CachedAdapter(
            new GoogleStorageAdapter($storage, $storage->bucket($gcloudBucket), $pathPrefix),
            new Memory()
        );
    }
}
