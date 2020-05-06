<?php

namespace App\RepublicanSilence\TagExtractor;

use App\Entity\Adherent;
use App\Entity\CitizenProjectMembership;
use App\Entity\PostAddress;

class CitizenProjectReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        if (null === $slug) {
            return [];
        }

        /** @var CitizenProjectMembership $membership */
        foreach ($adherent->getCitizenProjectMemberships()->getCitizenProjectAdministratorMemberships() as $membership) {
            $citizenProject = $membership->getCitizenProject();

            if ($citizenProject->getSlug() === $slug) {
                $tags = [
                    $citizenProject->getPostalCode(),
                    $citizenProject->getCityName(),
                    $citizenProject->getCountryName(),
                    $citizenProject->getCountry(),
                ];

                if (PostAddress::FRANCE === $citizenProject->getCountry()) {
                    $tags[] = \mb_substr($citizenProject->getPostalCode(), 0, 2);
                }

                return $tags;
            }
        }

        return [];
    }
}
