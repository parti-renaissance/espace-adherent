<?php

namespace AppBundle\Repository;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use AppBundle\Entity\IdeasWorkshop\ThreadStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ThreadCommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ThreadComment::class);
    }

    public function countThreadComments(Idea $idea): int
    {
        return $this
            ->createQueryBuilder('threadComment')
            ->select('COUNT(threadComment)')
            ->innerJoin('threadComment.thread', 'thread')
            ->innerJoin('thread.answer', 'answer')
            ->innerJoin('answer.idea', 'idea')
            ->where('idea = :idea')
            ->setParameter('idea', $idea)
            ->andWhere('threadComment.deletedAt IS NULL')
            ->andWhere('thread.status != :status')
            ->setParameter('status', ThreadStatusEnum::DELETED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
