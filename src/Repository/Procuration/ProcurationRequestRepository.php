<?php

namespace App\Repository\Procuration;

use App\Entity\ProcurationV2\ProcurationRequest;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProcurationRequestRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProcurationRequest::class);
    }

    /**
     * @return ProcurationRequest[]
     */
    public function findAllToRemind(): array
    {
        return $this->createQueryBuilder('pr')
            ->andWhere('pr.remindedAt IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(ProcurationRequest $procurationRequest): void
    {
        $this->_em->persist($procurationRequest);
        $this->_em->flush();
    }
}
