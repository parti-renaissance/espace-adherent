<?php

namespace App\Collection;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Doctrine\Common\Collections\ArrayCollection;

class TerritorialCouncilMembershipCollection extends ArrayCollection
{
    public function getPresident(): ?TerritorialCouncilMembership
    {
        /** @var TerritorialCouncilMembership $membership */
        foreach ($this as $membership) {
            if ($membership->hasQuality(TerritorialCouncilQualityEnum::REFERENT)) {
                return $membership;
            }
        }

        return null;
    }
}
