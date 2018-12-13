<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\ThreadCommentStatusEnum;
use AppBundle\Entity\IdeasWorkshop\VoteTypeEnum;
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
            ->andWhere('threads.status IN (:status)')
            ->setParameter('status', ThreadCommentStatusEnum::VISIBLE_STATUSES)
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
            ->andWhere('thread.status IN (:status)')
            ->setParameter('status', ThreadCommentStatusEnum::VISIBLE_STATUSES)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countVotesByType(Idea $idea): array
    {
        $votes = $this
            ->createQueryBuilder('idea')
            ->select('vote.type, COUNT(idea) as count')
            ->innerJoin('idea.votes', 'vote')
            ->where('idea = :idea')
            ->setParameter('idea', $idea)
            ->groupBy('vote.type')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_replace(
            array_fill_keys(VoteTypeEnum::toArray(), 0),
            array_column($votes, 'count', 'type')
        );
    }

    public function getAdherentVotesForIdea(Idea $idea, Adherent $adherent): array
    {
        $votes = $this
            ->createQueryBuilder('idea')
            ->select('vote.type')
            ->innerJoin('idea.votes', 'vote')
            ->where('idea = :idea')
            ->andWhere('vote.author = :author')
            ->setParameter('idea', $idea)
            ->setParameter('author', $adherent)
            ->groupBy('vote.type')
            ->getQuery()
            ->getArrayResult()
        ;

        array_walk($votes, function (&$vote) {
            $vote = $vote['type'];
        });

        return $votes;
    }
}
