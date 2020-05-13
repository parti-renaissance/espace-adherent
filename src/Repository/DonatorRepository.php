<?php

namespace App\Repository;

use App\Entity\Donator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DonatorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Donator::class);
    }

    public function findOneForMatching(string $emailAddress, string $firstName, string $lastName): ?Donator
    {
        return $this
            ->createQueryBuilder('donator')
            ->andWhere('donator.emailAddress = :emailAddress')
            ->andWhere('donator.firstName = :firstName')
            ->andWhere('donator.lastName = :lastName')
            ->setParameter('emailAddress', $emailAddress)
            ->setParameter('firstName', $firstName)
            ->setParameter('lastName', $lastName)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
