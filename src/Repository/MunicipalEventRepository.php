<?php

namespace AppBundle\Repository;

use AppBundle\Entity\MunicipalEvent;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MunicipalEventRepository extends EventRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MunicipalEvent::class);
    }
}
