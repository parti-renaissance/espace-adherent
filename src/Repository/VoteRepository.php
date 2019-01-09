<?php

namespace AppBundle\Repository;

use AppBundle\Entity\IdeasWorkshop\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VoteRepository extends ServiceEntityRepository
{
    use AuthorTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vote::class);
    }
}
