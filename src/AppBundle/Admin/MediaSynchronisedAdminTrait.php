<?php

namespace AppBundle\Admin;

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

    public function setStorage(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    public function setGlide(Server $glide)
    {
        $this->glide = $glide;
    }

    public function prePersist($object)
    {
        $this->storage->put(
            'images/'.$object->getMedia()->getPath(),
            file_get_contents($object->getMedia()->getFile()->getPathname())
        );

        $this->glide->deleteCache('images/'.$object->getMedia()->getPath());
    }

    public function preUpdate($object)
    {
        if ($object->getMedia()->getFile()) {
            $this->storage->put(
                'images/'.$object->getMedia()->getPath(),
                file_get_contents($object->getMedia()->getFile()->getPathname())
            );

            $this->glide->deleteCache('images/'.$object->getMedia()->getPath());
        }
    }
}
