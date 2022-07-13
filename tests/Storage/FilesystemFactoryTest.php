<?php

namespace Tests\App\Storage;

use App\Storage\FilesystemFactory;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

class FilesystemFactoryTest extends TestCase
{
    public function testCreateLocal(): void
    {
        $tmp = sys_get_temp_dir().'/storage-local/';

        $filesystem = FilesystemFactory::createLocal($tmp);

        self::assertInstanceOf(Filesystem::class, $filesystem);

        $adapter = $filesystem->getAdapter();

        self::assertInstanceOf(Local::class, $adapter);
        self::assertSame($tmp, $filesystem->getPathPrefix());
        self::assertDirectoryExists($tmp);

        rmdir($tmp);
    }

    public function testCreateGoogleStorage(): void
    {
        $filesystem = FilesystemFactory::createGoogleStorage('foo-bucket', 'path/to/dir');

        self::assertInstanceOf(Filesystem::class, $filesystem);

        $adapter = $filesystem->getAdapter();

        self::assertInstanceOf(CachedAdapter::class, $adapter);
        self::assertInstanceOf(GoogleStorageAdapter::class, $adapter->getAdapter());
        self::assertSame('path/to/dir/', $adapter->getPathPrefix());
    }
}
