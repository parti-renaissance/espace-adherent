<?php

namespace AppBundle\Repository;

use AppBundle\Entity\DeputyManagedUsersMessage;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DeputyManagedUsersMessageRepository extends ManagedUsersMessageRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DeputyManagedUsersMessage::class);
    }
}
