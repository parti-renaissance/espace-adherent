<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CandidacyInvitationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
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
                'pending' => CandidacyInvitation::STATUS_PENDING,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
