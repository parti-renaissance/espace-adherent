<?php

namespace App\Repository;

use App\Entity\CitizenProject;
use App\Entity\CitizenProjectCommitteeSupport;
use App\Entity\Committee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CitizenProjectCommitteeSupportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CitizenProjectCommitteeSupport::class);
    }

    public function findOneByCommitteeAndCitizenProject(
        Committee $committee,
        CitizenProject $citizenProject
    ): ?CitizenProjectCommitteeSupport {
        return $this->findOneBy(['committee' => $committee, 'citizenProject' => $citizenProject]);
    }
}
