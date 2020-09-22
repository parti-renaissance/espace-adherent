<?php

namespace App\Collection;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use Doctrine\Common\Collections\ArrayCollection;

class TerritorialCouncilMembershipCollection extends ArrayCollection
{
    public function getPresident(): ?TerritorialCouncilMembership
    {
        /** @var TerritorialCouncilMembership $membership */
        foreach ($this as $membership) {
            if ($membership->isPresident()) {
                return $membership;
            }
        }

        return null;
    }
}
