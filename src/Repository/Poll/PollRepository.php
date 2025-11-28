<?php

declare(strict_types=1);

namespace App\Repository\Poll;

use App\Entity\Poll\Poll;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PollRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use UnpublishPollTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poll::class);
    }
}
