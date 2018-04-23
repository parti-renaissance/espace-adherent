<?php

namespace AppBundle\RepublicanSilence\AdherentZone;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProjectMembership;
use Symfony\Component\HttpFoundation\Request;

class CitizenProjectHostZoneRetriever implements AdherentZoneRetrieverInterface
{
    public function getAdherentZone(Adherent $adherent, Request $request): array
    {
        if (!$request->attributes->has('slug')) {
            return [];
        }

        $citizenProjectSlug = $request->attributes->get('slug');

        /** @var CitizenProjectMembership $membership */
        foreach ($adherent->getCitizenProjectMemberships() as $membership) {
            $committee = $membership->get;
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
