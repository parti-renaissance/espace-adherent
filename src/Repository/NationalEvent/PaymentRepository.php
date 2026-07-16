<?php

declare(strict_types=1);

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

    /**
     * @return Payment[]
     */
    public function findToCheck(): array
    {
        return $this->createQueryBuilder('p')
            // Paybox payments are confirmed by their IPN and have no checkout session to poll: only Worldline
            // payments (donation IS NULL) belong to this reconciliation.
            ->where('p.donation IS NULL')
            // The OR must stay parenthesised: AND binds tighter, so an unparenthesised OR would let expired
            // payments escape the two conditions above.
            ->andWhere('(p.status IN (:statuses) OR (p.status = :expired_status AND p.expiredCheckedAt IS NULL))')
            ->andWhere('p.createdAt < :from')
            ->setParameter('from', new \DateTime()->modify('-20 minutes'))
            ->setParameter('statuses', [PaymentStatusEnum::PENDING, PaymentStatusEnum::UNKNOWN])
            ->setParameter('expired_status', PaymentStatusEnum::EXPIRED)
            ->orderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
