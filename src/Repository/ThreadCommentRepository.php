<?php

namespace App\Repository;

use App\Entity\IdeasWorkshop\ThreadComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ThreadCommentRepository extends ServiceEntityRepository
{
    use AuthorTrait;
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThreadComment::class);
    }
}
