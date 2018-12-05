<?php

namespace AppBundle\Repository;

use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\ThreadStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IdeaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Idea::class);
    }

    public function countIdeaContributors(Idea $idea): int
    {
        return $this->createQueryBuilder('idea')
            ->select('COUNT(adherent)')
            ->innerJoin('idea.answers', 'answers')
            ->innerJoin('answers.threads', 'threads')
            ->innerJoin('threads.comments', 'comments')
            ->innerJoin('comments.author', 'adherent')
            ->where('idea = :idea')
            ->setParameter('idea', $idea)
            ->andWhere('comments.deletedAt IS NULL')
            ->andWhere('threads.status = :status')
            ->setParameter('status', ThreadStatusEnum::APPROVED)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countThreadComments(Idea $idea): int
    {
        return $this
            ->createQueryBuilder('idea')
            ->select('COUNT(threadComment)')
            ->innerJoin('idea.answers', 'answer')
            ->innerJoin('answer.threads', 'thread')
            ->innerJoin('thread.comments', 'threadComment')
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
