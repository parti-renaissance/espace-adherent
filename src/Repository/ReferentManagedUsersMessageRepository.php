<?php

namespace App\Repository;

use App\Entity\ReferentManagedUsersMessage;
use Doctrine\Common\Persistence\ManagerRegistry;

class ReferentManagedUsersMessageRepository extends ManagedUsersMessageRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferentManagedUsersMessage::class);
    }
}
