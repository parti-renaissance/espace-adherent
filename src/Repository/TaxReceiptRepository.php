<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Donator;
use App\Entity\TaxReceipt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaxReceiptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaxReceipt::class);
    }

    /**
     * @return TaxReceipt[]
     */
    public function findAllByAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('tr')
            ->addSelect('donator')
            ->innerJoin('tr.donator', 'donator')
            ->andWhere('donator.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->orderBy('tr.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TaxReceipt[]
     */
    public function findAllByDonator(Donator $donator): array
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.donator = :donator')
            ->setParameter('donator', $donator)
            ->orderBy('tr.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
