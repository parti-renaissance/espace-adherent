<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CitizenProjectCommitteeSupport;
use AppBundle\Entity\Committee;
use Doctrine\ORM\EntityRepository;

class CitizenProjectCommitteeSupportRepository extends EntityRepository
{
    public function findByCommittee(Committee $committee): ?CitizenProjectCommitteeSupport
    {
        return $this->findOneBy(['committee' => $committee]);
    }
}
