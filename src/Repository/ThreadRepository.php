<?php

namespace AppBundle\Repository;

use AppBundle\Entity\IdeasWorkshop\Thread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ThreadRepository extends ServiceEntityRepository
{
    use AuthorTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Thread::class);
    }
}
