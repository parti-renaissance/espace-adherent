<?php

namespace App\Repository\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VotersList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class VoterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voter::class);
    }

    public function findForAdherent(Adherent $adherent): ?Voter
    {
        return $this->findOneBy(['adherent' => $adherent]);
    }

    public function countForElection(Election $election): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(1)')
            ->innerJoin(VotersList::class, 'vl', Join::ON)
            ->where('vl.election = :election')
            ->setParameter('election', $election)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
