<?php

namespace App\Admin\HomeBlock;

use App\Entity\HomeBlock;

class RenaissanceHomeBlockAdmin extends AbstractHomeBlockAdmin
{
    protected $baseRoutePattern = 'renaissance-homeblock';
    protected $baseRouteName = 'renaissance-homeblock';

    /** @param HomeBlock $object */
    public function prePersist($object)
    {
        $object->setForRenaissance(true);
    }

    /** @param HomeBlock $object */
    public function preUpdate($object)
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
