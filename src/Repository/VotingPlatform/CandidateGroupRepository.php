<?php

declare(strict_types=1);

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\VoteChoice;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\VotingPlatform\CandidateGroup>
 */
class CandidateGroupRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateGroup::class);
    }

    /**
     * @return CandidateGroup[]
     */
    public function findByUuids(array $uuids): array
    {
        return $this->createQueryBuilder('cg')
            ->addSelect('candidate')
            ->innerJoin('cg.candidates', 'candidate')
            ->where('cg.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult()
        ;
    }

    public function aggregatePoolResults(ElectionPool $electionPool): array
    {
        return $this->createQueryBuilder('candidate_group', 'candidate_group.id')
            ->select('candidate_group.id')
            ->addSelect(
                \sprintf(
                    '(SELECT COUNT(1)
                FROM %s AS vote_choice
                WHERE vote_choice.candidateGroup = candidate_group) AS count',
                    VoteChoice::class
                )
            )
            ->where('candidate_group.electionPool = :election_pool')
            ->setParameters(new ArrayCollection([new Parameter('election_pool', $electionPool)]))
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
