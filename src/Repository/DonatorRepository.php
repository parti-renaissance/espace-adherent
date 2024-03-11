<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Donator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DonatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donator::class);
    }

    public function findOneForMatching(string $emailAddress, string $firstName, string $lastName): ?Donator
    {
        return $this
            ->createQueryBuilder('donator')
            ->addSelect('adherent')
            ->leftJoin('donator.adherent', 'adherent')
            ->andWhere('donator.emailAddress = :emailAddress')
            ->andWhere('donator.firstName = :firstName')
            ->andWhere('donator.lastName = :lastName')
            ->setParameters([
                'emailAddress' => $emailAddress,
                'firstName' => $firstName,
                'lastName' => $lastName,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function updateDonatorEmail(Adherent $adherent): void
    {
        $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'donator')
            ->where('donator.adherent = :adherent')
            ->set('donator.emailAddress', ':email')
            ->setParameter('adherent', $adherent)
            ->setParameter('email', $adherent->getEmailAddress())
            ->getQuery()
            ->execute()
        ;
    }
}
