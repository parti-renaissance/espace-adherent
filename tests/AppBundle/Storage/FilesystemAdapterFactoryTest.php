<?php

namespace Tests\AppBundle\Storage;

use AppBundle\Storage\FilesystemAdapterFactory;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

class FilesystemAdapterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateDevAdapter()
    {
        $tmp = sys_get_temp_dir().'/en-marche-filesystem-local';

        $adapter = FilesystemAdapterFactory::createAdapter('dev', $tmp, null, null, null);

        $this->assertInstanceOf(Local::class, $adapter);
        $this->assertFileExists($tmp);

        rmdir($tmp);
    }

    public function testCreateProdAdapter()
    {
        $adapter = FilesystemAdapterFactory::createAdapter('prod', '', 'project-id', __DIR__.'/../../Fixtures/gcloud-service-key.json', 'project-bucket');

        $this->assertInstanceOf(CachedAdapter::class, $adapter);
        $this->assertInstanceOf(GoogleStorageAdapter::class, $adapter->getAdapter());
    }
}
