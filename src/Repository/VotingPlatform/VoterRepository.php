<?php

namespace AppBundle\Repository\VotingPlatform;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\VotingPlatform\Voter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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
}
