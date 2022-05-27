<?php

namespace App\Storage;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory;
use League\Flysystem\Filesystem;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

class FilesystemFactory
{
    public static function createLocal(string $localPath): Filesystem
    {
        return self::create(new Local($localPath));
    }

    public static function createGoogleStorage(string $gcloudBucket, string $pathPrefix = null): Filesystem
    {
        $storage = new StorageClient();

        return self::create(new CachedAdapter(
            new GoogleStorageAdapter($storage, $storage->bucket($gcloudBucket), $pathPrefix),
            new Memory(),
        ));
    }

    private static function create(AdapterInterface $adapter): Filesystem
    {
        return new Filesystem($adapter);
    }
}
