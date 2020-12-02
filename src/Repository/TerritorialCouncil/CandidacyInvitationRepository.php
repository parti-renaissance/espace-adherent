<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInvitationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CandidacyInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidacyInvitation::class);
    }

    /**
     * @return CandidacyInvitation[]
     */
    public function findAllPendingForMembership(TerritorialCouncilMembership $membership, Election $election): array
    {
        return $this->createQueryBuilder('invitation')
            ->innerJoin('invitation.candidacy', 'candidacy')
            ->where('invitation.membership = :membership')
            ->andWhere('candidacy.election = :election')
            ->andWhere('invitation.status = :pending')
            ->setParameters([
                'membership' => $membership,
                'election' => $election,
                'pending' => CandidacyInvitationInterface::STATUS_PENDING,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
