<?php

namespace App\Repository\Contribution;

use App\Entity\Adherent;
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
        $payments = $this->createQueryBuilder('payment')
            ->where('payment.ohmeId = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getResult()
        ;

        return $payments[0] ?? null;
    }

    public function save(Payment $payment): void
    {
        $this->_em->persist($payment);
        $this->_em->flush();
    }

    public function getTotalPaymentByYearForAdherent(Adherent $adherent): array
    {
        return array_column($this->createQueryBuilder('payment')
            ->select('YEAR(payment.date) AS year')
            ->addSelect('SUM(payment.amount) AS total')
            ->where('payment.adherent = :adherent')
            ->andWhere('payment.status IN (:status)')
            ->setParameters([
                'adherent' => $adherent,
                'status' => [
                    'paid_out',
                    'confirmed',
                    'cheque_cashed',
                ],
            ])
            ->groupBy('year')
            ->orderBy('year', 'DESC')
            ->getQuery()
            ->getResult(), 'total', 'year'
        );
    }
}
