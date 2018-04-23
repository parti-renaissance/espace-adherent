<?php

namespace AppBundle\RepublicanSilence\AdherentZone;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeMembership;
use Symfony\Component\HttpFoundation\Request;

class CommitteeHostZoneRetriever implements AdherentZoneRetrieverInterface
{
    public function getAdherentZone(Adherent $adherent, Request $request): array
    {
        if (!$request->attributes->has('slug')) {
            return [];
        }

        $committeeSlug = $request->attributes->get('slug');

        /** @var CommitteeMembership $membership */
        foreach ($adherent->getMemberships() as $membership) {
            $committee = $membership->getCommittee();
            if ($committee->getSlug() === $committeeSlug) {
                return [
                    $committee->getPostalCode(),
                    $committee->getCityName(),
                    $committee->getCountryName(),
                    $committee->getCountry(),
                ];
            }
        }

        return [];
    }
}
