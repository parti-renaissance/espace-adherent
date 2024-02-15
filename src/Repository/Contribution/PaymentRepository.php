<?php

namespace App\Repository\Contribution;

use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use App\Entity\Contribution\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function findOneByOhmeIdentifier(string $identifier): ?Payment
    {
        return $this->createQueryBuilder('payment')
            ->where('payment.ohmeId = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function save(Payment $payment): void
    {
        $this->_em->persist($payment);
        $this->_em->flush();
    }
}
