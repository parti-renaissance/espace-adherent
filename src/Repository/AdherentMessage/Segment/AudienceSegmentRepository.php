<?php

declare(strict_types=1);

namespace App\Repository\AdherentMessage\Segment;

use App\Entity\AdherentMessage\Segment\AudienceSegment;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AudienceSegmentRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AudienceSegment::class);
    }
}
