<?php

namespace App\Repository\Poll;

use App\Entity\Poll\NationalPoll;
use Doctrine\Persistence\ManagerRegistry;

class NationalPollRepository extends AbstractPollRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NationalPoll::class);
    }
}
