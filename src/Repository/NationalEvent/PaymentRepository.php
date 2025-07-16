<?php

namespace App\Repository\NationalEvent;

use App\Entity\NationalEvent\Payment;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PaymentRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function cancelWaitingPayments(\DateTime $date): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.status', ':new_status')
            ->where('p.status = :pending_status')
            ->andWhere('p.createdAt < :date')
            ->setParameter('new_status', PaymentStatusEnum::EXPIRED)
            ->setParameter('pending_status', PaymentStatusEnum::PENDING)
            ->setParameter('date', $date)
            ->getQuery()
            ->execute()
        ;
    }
}
