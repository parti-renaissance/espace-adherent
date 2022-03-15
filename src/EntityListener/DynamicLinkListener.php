<?php

namespace App\EntityListener;

use App\Firebase\DynamicLinks\DynamicLinkObjectInterface;
use App\Firebase\DynamicLinks\Manager;

class DynamicLinkListener
{
    private Manager $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function prePersist(DynamicLinkObjectInterface $object): void
    {
        if ($link = $this->manager->create($object)) {
            $object->setDynamicLink((string) $link);
        }
    }
}
