<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ReferentManagedUsersMessage;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferentManagedUsersMessageRepository extends ManagedUsersMessageRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReferentManagedUsersMessage::class);
    }
}
