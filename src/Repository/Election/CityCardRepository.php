<?php

namespace AppBundle\Repository\Election;

use AppBundle\Entity\Election\CityCard;
use AppBundle\Repository\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CityCardRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CityCard::class);
    }

    public function getIterator(): \Iterator
    {
        return $this->createQueryBuilder('cd')->getQuery()->iterate();
    }
}
