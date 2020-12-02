<?php

namespace App\Repository;

use App\Entity\DeputyManagedUsersMessage;
use Doctrine\Common\Persistence\ManagerRegistry;

class DeputyManagedUsersMessageRepository extends ManagedUsersMessageRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeputyManagedUsersMessage::class);
    }
}
