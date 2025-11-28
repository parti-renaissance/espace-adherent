<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Transaction;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findByPayboxTransactionId(string $transactionId): ?Transaction
    {
        return $this->createQueryBuilder('transaction')
            ->where('transaction.payboxTransactionId = :transactionId')
            ->setParameter('transactionId', $transactionId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Transaction[]
     */
    public function findAllTransactionByAdherentIdOrEmail(Adherent $adherent, bool $onlySuccess = true): array
    {
        $qb = $this->createQueryBuilder('transaction')
            ->addSelect('donation')
            ->innerJoin('transaction.donation', 'donation')
            ->innerJoin('donation.donator', 'donator')
            ->leftJoin('donator.adherent', 'adherent')
            ->andWhere('donator.emailAddress = :email OR adherent = :adherent')
            ->setParameters([
                'email' => $adherent->getEmailAddress(),
                'adherent' => $adherent,
            ])
            ->orderBy('transaction.payboxDateTime', 'DESC')
        ;

        if ($onlySuccess) {
            $qb

                ->andWhere('transaction.payboxResultCode = :resultCode')
                ->setParameter('resultCode', Transaction::PAYBOX_SUCCESS)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Total amount in cents
     */
    public function getTotalAmountInCentsByEmail(string $email): int
    {
        $now = new Chronos();

        return (int) $this->createQueryBuilder('transaction')
            ->innerJoin('transaction.donation', 'donation')
            ->innerJoin('donation.donator', 'donator')
            ->select('SUM(donation.amount)')
            ->where('donator.emailAddress = :email')
            ->andWhere('transaction.payboxResultCode = :success_code')
            ->andWhere('transaction.payboxDateTime BETWEEN :first_day_of_year AND :last_day_of_year')
            ->setParameters([
                'email' => $email,
                'success_code' => Transaction::PAYBOX_SUCCESS,
                'first_day_of_year' => $now->format('Y/01/01 00:00:00'),
                'last_day_of_year' => $now->format('Y/12/31 23:59:59'),
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
