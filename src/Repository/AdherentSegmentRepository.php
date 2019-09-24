<?php

namespace AppBundle\Repository;

use AppBundle\Entity\AdherentSegment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AdherentSegmentRepository extends ServiceEntityRepository
{
    use AuthorTrait;
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentSegment::class);
    }
}
