<?php

namespace App\Repository;

use App\Entity\CertificationRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
            ->createPendingQueryBuilder($interval)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPreRefused(string $interval): iterable
    {
        return $this
            ->createPendingQueryBuilder($interval)
            ->andWhere('cr.ocrStatus = :status_pre_refused')
            ->setParameter('status_pre_refused', CertificationRequest::OCR_STATUS_PRE_REFUSED)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPreApproved(string $interval): iterable
    {
        return $this
            ->createPendingQueryBuilder($interval)
            ->andWhere('cr.ocrStatus = :status_pre_approved')
            ->setParameter('status_pre_approved', CertificationRequest::OCR_STATUS_PRE_APPROVED)
            ->getQuery()
            ->getResult()
        ;
    }

    private function createPendingQueryBuilder(string $interval): QueryBuilder
    {
        return $this
            ->createQueryBuilder('cr')
            ->andWhere('cr.status = :status_pending')
            ->andWhere('cr.createdAt <= :created_at')
            ->setParameters([
                'status_pending' => CertificationRequest::STATUS_PENDING,
                'created_at' => $this->createDateTimeForInterval($interval),
            ])
        ;
    }

    private function createDateTimeForInterval(string $interval): \DateTime
    {
        $date = new \DateTime('now');
        $date->add(\DateInterval::createFromDateString($interval));

        return $date;
    }
}
