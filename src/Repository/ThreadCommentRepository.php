<?php

namespace AppBundle\Repository;

use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ThreadCommentRepository extends ServiceEntityRepository
{
    use AuthorTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ThreadComment::class);
    }
}
