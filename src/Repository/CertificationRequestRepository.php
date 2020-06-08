<?php

namespace App\Repository;

use App\Entity\CertificationRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CertificationRequestRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CertificationRequest::class);
    }

    public function findPending(string $interval): iterable
    {
        return $this
            ->createQueryBuilder('cr')
            ->andWhere('cr.status = :status_pending')
            ->andWhere('cr.createdAt <= :created_at')
            ->setParameters([
                'status_pending' => CertificationRequest::STATUS_PENDING,
                'created_at' => $this->createDateTimeForInterval($interval),
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    private function createDateTimeForInterval(string $interval): \DateTime
    {
        $date = new \DateTime('now');
        $date->add(\DateInterval::createFromDateString($interval));

        return $date;
    }
}
