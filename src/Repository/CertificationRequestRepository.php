<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CertificationRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CertificationRequestRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CertificationRequest::class);
    }

    public function findPending(\DateTimeInterface $createdBefore): iterable
    {
        return $this
            ->createPendingQueryBuilder($createdBefore)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDocumentsToDelete(\DateTimeInterface $createdBefore): iterable
    {
        return $this
            ->createQueryBuilder('cr')
            ->andWhere('cr.createdAt <= :created_at')
            ->andWhere('cr.documentName IS NOT NULL')
            ->setParameters([
                'created_at' => $createdBefore,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPreRefused(\DateTimeInterface $createdBefore): iterable
    {
        return $this
            ->createPendingQueryBuilder($createdBefore)
            ->andWhere('cr.ocrStatus = :status_pre_refused')
            ->setParameter('status_pre_refused', CertificationRequest::OCR_STATUS_PRE_REFUSED)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPreApproved(\DateTimeInterface $createdBefore): iterable
    {
        return $this
            ->createPendingQueryBuilder($createdBefore)
            ->andWhere('cr.ocrStatus = :status_pre_approved')
            ->setParameter('status_pre_approved', CertificationRequest::OCR_STATUS_PRE_APPROVED)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllForDate(\DateTimeInterface $date): iterable
    {
        return $this
            ->createQueryBuilder('cr')
            ->andWhere('cr.status = :status_pending')
            ->andWhere('cr.createdAt LIKE :created_at')
            ->setParameters([
                'status_pending' => CertificationRequest::STATUS_PENDING,
                'created_at' => $date->format('Y-m-d').'%',
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    private function createPendingQueryBuilder(\DateTimeInterface $createdBefore): QueryBuilder
    {
        return $this
            ->createQueryBuilder('cr')
            ->andWhere('cr.status = :status_pending')
            ->andWhere('cr.createdAt <= :created_at')
            ->setParameters([
                'status_pending' => CertificationRequest::STATUS_PENDING,
                'created_at' => $createdBefore,
            ])
        ;
    }
}
