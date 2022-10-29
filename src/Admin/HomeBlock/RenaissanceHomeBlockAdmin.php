<?php

namespace App\Admin\HomeBlock;

use App\Entity\HomeBlock;

class RenaissanceHomeBlockAdmin extends AbstractHomeBlockAdmin
{
    protected $baseRoutePattern = 'renaissance-homeblock';
    protected $baseRouteName = 'renaissance-homeblock';

    /** @param HomeBlock $object */
    protected function prePersist(object $object): void
    {
        $object->setForRenaissance(true);
    }

    /** @param HomeBlock $object */
    protected function preUpdate(object $object): void
    {
        if (!$object->isForRenaissance()) {
            $object->setForRenaissance(true);
        }
    }

    protected function isRenaissanceHomeBlock(): bool
    {
        return true;
    }
}
