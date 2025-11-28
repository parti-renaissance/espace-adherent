<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\EntityMediaInterface;
use League\Flysystem\FilesystemOperator;
use League\Glide\Server;
use Symfony\Contracts\Service\Attribute\Required;

trait MediaSynchronisedAdminTrait
{
    protected FilesystemOperator $storage;
    protected Server $glide;

    #[Required]
    public function setStorage(FilesystemOperator $defaultStorage): void
    {
        $this->storage = $defaultStorage;
    }

    #[Required]
    public function setGlide(Server $glide): void
    {
        $this->glide = $glide;
    }

    /**
     * @param EntityMediaInterface $object
     */
    protected function prePersist(object $object): void
    {
        if (!$object->getMedia() || !$object->getMedia()->getFile()) {
            return;
        }

        $this->storage->write(
            'images/'.$object->getMedia()->getPath(),
            file_get_contents($object->getMedia()->getFile()->getPathname())
        );

        $this->glide->deleteCache('images/'.$object->getMedia()->getPath());
    }

    /**
     * @param EntityMediaInterface $object
     */
    protected function preUpdate(object $object): void
    {
        if (!$object->getMedia() || !$object->getMedia()->getFile()) {
            return;
        }

        $this->storage->write(
            'images/'.$object->getMedia()->getPath(),
            file_get_contents($object->getMedia()->getFile()->getPathname())
        );

        $this->glide->deleteCache('images/'.$object->getMedia()->getPath());
    }
}
