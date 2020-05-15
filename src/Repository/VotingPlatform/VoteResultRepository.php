<?php

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\VoteResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VoteResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteResult::class);
    }

    public function getResults(Election $election): array
    {
        $results = $this->createQueryBuilder('vr')
            ->addSelect('vc', 'cg', 'c')
            ->innerJoin('vr.voteChoices', 'vc')
            ->leftJoin('vc.candidateGroup', 'cg')
            ->leftJoin('cg.candidates', 'c')
            ->where('vr.election = :election')
            ->setParameter('election', $election)
            ->getQuery()
            ->getArrayResult()
        ;

        $aggregated = [
            'candidates' => [],
            'resume' => [],
        ];

        foreach ($results as $vote) {
            foreach ($vote['voteChoices'] as $index => $voteChoice) {
                if (!isset($aggregated['resume'][$index])) {
                    $aggregated['resume'][$index] = [
                        'blank' => 0,
                        'participated' => 0,
                        'expressed' => 0,
                    ];
                }

                if (true === $voteChoice['isBlank']) {
                    ++$aggregated['resume'][$index]['blank'];
                } else {
                    ++$aggregated['resume'][$index]['expressed'];

                    $candidateGroupUuid = $voteChoice['candidateGroup']['uuid']->toString();

                    if (!isset($aggregated['candidates'][$candidateGroupUuid])) {
                        $aggregated['candidates'][$candidateGroupUuid] = 0;
                    }

                    ++$aggregated['candidates'][$candidateGroupUuid];
                }

                ++$aggregated['resume'][$index]['participated'];
            }
        }

        // Sort candidates list
        arsort($aggregated['candidates']);

        return [
            'votes' => $results,
            'aggregated' => $aggregated,
        ];
    }
}
