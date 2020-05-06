<?php

namespace App\Repository\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\VotersList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VotersListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VotersList::class);
    }

    public function existsForElection(Adherent $adherent, string $electionUuid): bool
    {
        return 0 < (int) $this->createQueryBuilder('list')
            ->select('COUNT(1)')
            ->innerJoin('list.voters', 'voter')
            ->innerJoin('list.election', 'election')
            ->where('voter.adherent = :adherent AND election.uuid = :election_uuid')
            ->setParameters([
                'adherent' => $adherent,
                'election_uuid' => $electionUuid,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
