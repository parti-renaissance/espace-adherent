<?php

namespace AppBundle\Repository;

use AppBundle\Entity\DonatorIdentifier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DonatorIdentifierRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DonatorIdentifier::class);
    }

    public function findLastIdentifier(): ?DonatorIdentifier
    {
        return $this
            ->createQueryBuilder('donator')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
