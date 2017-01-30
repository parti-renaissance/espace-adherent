<?php

namespace AppBundle\Collection;

use AppBundle\Entity\CommitteeMembership;
use Doctrine\Common\Collections\ArrayCollection;

class CommitteeMembershipCollection extends ArrayCollection
{
    public function getAdherentUuids()
    {
        return array_map(
            function (CommitteeMembership $membership) {
                return (string) $membership->getAdherentUuid();
            },
            $this->getValues()
        );
    }
}
