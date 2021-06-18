<?php

namespace App\Repository\Poll;

use App\Entity\Poll\Poll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PollRepository extends ServiceEntityRepository
{
    use UnpublishPollTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poll::class);
    }
}
