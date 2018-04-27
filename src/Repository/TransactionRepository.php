<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Transaction;
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
     * @param string $emailAddress
     *
     * @return Transaction[]
     */
    public function findAllSuccessfulTransactionByEmail(string $emailAddress): array
    {
        return $this->createQueryBuilder('transaction')
            ->addSelect('donation')
            ->innerJoin('transaction.donation', 'donation')
            ->andWhere('donation.emailAddress = :email')
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
}
