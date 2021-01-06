<?php

namespace App\Repository;

use App\Entity\TonMacronFriendInvitation;
use Doctrine\Persistence\ManagerRegistry;

class TonMacronFriendInvitationRepository extends InteractiveInvitationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TonMacronFriendInvitation::class);
    }
}
