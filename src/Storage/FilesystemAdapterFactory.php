<?php

namespace App\Storage;

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
        $gcloudBucket
    ) {
        if ('prod' !== $environment) {
            return new Local($localPath);
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
