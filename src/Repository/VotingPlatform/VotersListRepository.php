<?php

namespace AppBundle\Repository\VotingPlatform;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\VotingPlatform\Election;
use AppBundle\Entity\VotingPlatform\VotersList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VotersListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VotersList::class);
    }

    public function existsForElection(Adherent $adherent, Election $election): bool
    {
        return 0 < (int) $this->createQueryBuilder('list')
            ->select('COUNT(1)')
            ->innerJoin('list.voters', 'voter')
            ->where('voter.adherent = :adherent AND list.election = :election')
            ->setParameters([
                'adherent' => $adherent,
                'election' => $election,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
