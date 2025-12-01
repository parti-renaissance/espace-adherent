<?php

declare(strict_types=1);

namespace App\Repository\Procuration;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\ProcurationV2\ProcurationRequest>
 */
class ProcurationRequestRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProcurationRequest::class);
    }
}
