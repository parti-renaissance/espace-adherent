<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\IdeaStatusEnum;
use AppBundle\Entity\IdeasWorkshop\ThreadCommentStatusEnum;
use AppBundle\Entity\IdeasWorkshop\VoteTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IdeaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Idea::class);
    }

    public function getIdeaContributors(Idea $idea): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from(Adherent::class, 'adherent')
            ->select('adherent')
            ->join(ThreadComment::class, 'comment', Join::WITH, 'comment.author = adherent.id')
            ->join('comment.thread', 'thread')
            ->join(Answer::class, 'answer', Join::WITH, 'thread.answer = answer.id')
            ->join('answer.idea', 'idea')
            ->where('idea = :idea')
            ->setParameter('idea', $idea)
            ->andWhere('comment.deletedAt IS NULL')
            ->andWhere('thread.status IN (:status)')
            ->setParameter('status', ThreadCommentStatusEnum::VISIBLE_STATUSES)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countIdeaContributors(Idea $idea): int
    {
        return $this->createQueryBuilder('idea')
            ->select('COUNT(DISTINCT adherent)')
            ->innerJoin('idea.answers', 'answers')
            ->innerJoin('answers.threads', 'threads')
            ->innerJoin('threads.comments', 'comments')
            ->innerJoin('comments.author', 'adherent')
            ->where('idea = :idea')
            ->setParameter('idea', $idea)
            ->andWhere('comments.deletedAt IS NULL')
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
            ->select('vote.type, vote.id')
            ->innerJoin('idea.votes', 'vote')
            ->where('idea = :idea')
            ->andWhere('vote.author = :author')
            ->setParameter('idea', $idea)
            ->setParameter('author', $adherent)
            ->groupBy('vote.type')
            ->getQuery()
            ->getArrayResult()
        ;

        return array_reduce($votes, function ($result, $vote) {
            $result[$vote['type']] = $vote['id'];

            return $result;
        }, []);
    }

    public function removeNotFinalizedIdeas(Adherent $author): void
    {
        $qb = $this->createQueryBuilder('idea');

        $qb->delete()
            ->set('idea.author', $qb->expr()->literal(null))
            ->where('idea.author = :author')
            ->andWhere('idea.finalizedAt IS NULL OR idea.finalizedAt > :now')
            ->setParameter('author', $author)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute()
        ;
    }

    public function anonymizeFinalizedIdeas(Adherent $author): void
    {
        $this->createQueryBuilder('idea')
            ->update()
            ->set('idea.author', 'null')
            ->where('idea.author = :author')
            ->andWhere('idea.finalizedAt IS NOT NULL AND idea.finalizedAt <= :now')
            ->setParameter('author', $author)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute()
        ;
    }

    public function addStatusFilter($queryBuilder, string $alias, string $status)
    {
        switch ($status) {
            case IdeaStatusEnum::UNPUBLISHED:
                $queryBuilder->andWhere(sprintf('%s.enabled = 0', $alias));
                break;
            case IdeaStatusEnum::DRAFT:
                $queryBuilder->andWhere(sprintf('%s.publishedAt IS NULL', $alias));
                break;
            case IdeaStatusEnum::PENDING:
                $queryBuilder
                    ->andWhere(sprintf('%s.publishedAt IS NOT NULL AND %s.finalizedAt > :now', $alias, $alias))
                    ->setParameter('now', new \DateTime())
                ;
                break;
            case IdeaStatusEnum::FINALIZED:
                $queryBuilder->andWhere(sprintf('%s.finalizedAt <= :now', $alias))
                    ->setParameter('now', new \DateTime())
                ;
                break;
        }
    }
}
