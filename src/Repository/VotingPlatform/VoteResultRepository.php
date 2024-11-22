<?php

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\VoteResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VoteResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteResult::class);
    }

    /**
     * @return VoteResult[]
     */
    public function getResultsForRound(ElectionRound $electionRound): array
    {
        $result = $this->createQueryBuilder('vr')
            ->addSelect('vc', 'cg', 'c')
            ->innerJoin('vr.voteChoices', 'vc')
            ->leftJoin('vc.candidateGroup', 'cg')
            ->leftJoin('cg.candidates', 'c')
            ->where('vr.electionRound = :election_round')
            ->setParameter('election_round', $electionRound)
            ->getQuery()
            ->getResult()
        ;

        usort($result, function (VoteResult $a, VoteResult $b) {
            return strrev($a->getVoterKey()) <=> strrev($b->getVoterKey());
        });

        return $result;
    }

    public function getResultsForCandidate(
        int $adherentId,
        int $designationId,
        ?int $committeeId = null,
    ): array {
        $qb = $this->createQueryBuilder('vote_result')
            ->select('election_round.id AS election_round_id', 'COUNT(1) AS total')
            ->innerJoin('vote_result.electionRound', 'election_round')
            ->innerJoin('election_round.election', 'election')
            ->innerJoin('election.electionEntity', 'election_entity')
            ->innerJoin('election.designation', 'designation')
            ->innerJoin('vote_result.voteChoices', 'choice')
            ->innerJoin('choice.candidateGroup', 'candidate_group')
            ->innerJoin('candidate_group.candidates', 'candidates')
            ->innerJoin('candidates.adherent', 'adherent')
            ->where('adherent.id = :adherent_id AND designation.id = :designation_id')
            ->setParameters([
                'adherent_id' => $adherentId,
                'designation_id' => $designationId,
            ])
            ->groupBy('election.id', 'election_round.id')
        ;

        if ($committeeId) {
            $qb
                ->innerJoin('election_entity.committee', 'committee')
                ->andWhere('committee.id = :committee_id')
                ->setParameter('committee_id', $committeeId)
            ;
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function getVotes(Designation $designation): array
    {
        return $this->createQueryBuilder('vote_result')
            ->select('vote_result.voterKey AS Cle')
            ->addSelect('candidate_group.label AS Liste')
            ->addSelect('IF(choice.isBlank = 1, \'oui\', \'non\') AS Blanc')
            ->innerJoin('vote_result.electionRound', 'election_round')
            ->innerJoin('election_round.election', 'election')
            ->innerJoin('election.designation', 'designation')
            ->innerJoin('vote_result.voteChoices', 'choice')
            ->leftJoin('choice.candidateGroup', 'candidate_group')
            ->where('designation.id = :designation_id')
            ->setParameters([
                'designation_id' => $designation->getId(),
            ])
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
