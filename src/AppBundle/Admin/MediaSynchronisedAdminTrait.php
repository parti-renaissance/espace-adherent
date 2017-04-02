<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Clarification;
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

    /**
     * @param Clarification $clarification
     */
    public function prePersist($clarification)
    {
        $this->storage->put(
            'images/'.$clarification->getMedia()->getPath(),
            file_get_contents($clarification->getMedia()->getFile()->getPathname())
        );

        $this->glide->deleteCache('images/'.$clarification->getMedia()->getPath());
    }

    /**
     * @param Clarification $clarification
     */
    public function preUpdate($clarification)
    {
        if ($clarification->getMedia()->getFile()) {
            $this->storage->put(
                'images/'.$clarification->getMedia()->getPath(),
                file_get_contents($clarification->getMedia()->getFile()->getPathname())
            );

            $this->glide->deleteCache('images/'.$clarification->getMedia()->getPath());
        }
    }
}
