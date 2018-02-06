<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectCommitteeSupport;
use AppBundle\Entity\Committee;
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
