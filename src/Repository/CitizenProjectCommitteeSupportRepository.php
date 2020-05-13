<?php

namespace App\Repository;

use App\Entity\CitizenProject;
use App\Entity\CitizenProjectCommitteeSupport;
use App\Entity\Committee;
use Doctrine\ORM\EntityRepository;

class CitizenProjectCommitteeSupportRepository extends EntityRepository
{
    public function findOneByCommitteeAndCitizenProject(
        Committee $committee,
        CitizenProject $citizenProject
    ): ?CitizenProjectCommitteeSupport {
        return $this->findOneBy(['committee' => $committee, 'citizenProject' => $citizenProject]);
    }
}
