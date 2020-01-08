<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Transaction;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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
    public function findAllSuccessfulTransactionByEmail(string $emailAddress): array
    {
        return $this->createQueryBuilder('transaction')
            ->addSelect('donation')
            ->innerJoin('transaction.donation', 'donation')
            ->innerJoin('donation.donator', 'donator')
            ->andWhere('donator.emailAddress = :email')
            ->andWhere('transaction.payboxResultCode = :resultCode')
            ->setParameters([
                'resultCode' => Transaction::PAYBOX_SUCCESS,
                'email' => $emailAddress,
            ])
            ->orderBy('transaction.payboxDateTime', 'DESC')
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
