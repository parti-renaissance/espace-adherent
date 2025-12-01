<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdherentSegment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\AdherentSegment>
 */
class AdherentSegmentRepository extends ServiceEntityRepository
{
    use AuthorTrait;
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentSegment::class);
    }
}
