<?php

namespace App\Admin;

use App\Entity\EntityMediaInterface;
use League\Flysystem\Filesystem;
use League\Glide\Server;

trait MediaSynchronisedAdminTrait
{
    /**
     * @var Filesystem
     */
    protected $storage;

    /**
     * @var Server
     */
    protected $glide;

    public function setStorage(Filesystem $storage): void
    {
        $this->storage = $storage;
    }

    public function setGlide(Server $glide): void
    {
        $this->glide = $glide;
    }

    /**
     * @param EntityMediaInterface $object
     */
    public function prePersist($object): void
    {
        if (!$object->getMedia() || !$object->getMedia()->getFile()) {
            return;
        }

        $this->storage->put(
            'images/'.$object->getMedia()->getPath(),
            file_get_contents($object->getMedia()->getFile()->getPathname())
        );

        $this->glide->deleteCache('images/'.$object->getMedia()->getPath());
    }

    /**
     * @param EntityMediaInterface $object
     */
    public function preUpdate($object): void
    {
        if (!$object->getMedia() || !$object->getMedia()->getFile()) {
            return;
        }

        $this->storage->put(
            'images/'.$object->getMedia()->getPath(),
            file_get_contents($object->getMedia()->getFile()->getPathname())
        );

        $this->glide->deleteCache('images/'.$object->getMedia()->getPath());
    }
}
