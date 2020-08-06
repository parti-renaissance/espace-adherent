<?php

namespace App\Collection;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Doctrine\Common\Collections\ArrayCollection;

class TerritorialCouncilMembershipCollection extends ArrayCollection
{
    public function getPresident(): ?Adherent
    {
        /** @var TerritorialCouncilMembership $membership */
        foreach ($this->getValues() as $membership) {
            if ($membership->hasQuality(TerritorialCouncilQualityEnum::REFERENT)) {
                return $membership->getAdherent();
            }
        }

        return null;
    }
}
