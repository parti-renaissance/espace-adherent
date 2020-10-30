<?php

namespace App\Repository;

use App\Entity\CommitteeCandidacyInvitation;
use App\Entity\CommitteeElection;
use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInvitationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitteeCandidacyInvitationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommitteeCandidacyInvitation::class);
    }

    /**
     * @return CommitteeCandidacyInvitation[]
     */
    public function findAllPendingForMembership(CommitteeMembership $membership, CommitteeElection $election): array
    {
        return $this->createQueryBuilder('invitation')
            ->innerJoin('invitation.candidacy', 'candidacy')
            ->where('invitation.membership = :membership')
            ->andWhere('candidacy.committeeElection = :election')
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
