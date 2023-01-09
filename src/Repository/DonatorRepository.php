<?php

namespace App\Repository;

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

    public function updateDonatorEmail(string $oldEmail, string $newEmail): void
    {
        $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'donator')
            ->where('donator.emailAddress = :old_email')
            ->set('donator.emailAddress', ':new_email')
            ->setParameter('old_email', $oldEmail)
            ->setParameter('new_email', $newEmail)
            ->getQuery()
            ->execute()
        ;
    }
}
