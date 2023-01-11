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
        $donators = $this
            ->createQueryBuilder('donator')
            ->andWhere('donator.emailAddress = :emailAddress')
            ->andWhere('donator.firstName = :firstName')
            ->andWhere('donator.lastName = :lastName')
            ->setParameter('emailAddress', $emailAddress)
            ->setParameter('firstName', $firstName)
            ->setParameter('lastName', $lastName)
            ->getQuery()
            ->getResult()
        ;

        if ($donators) {
            return current($donators);
        }

        return null;
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
