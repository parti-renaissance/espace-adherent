<?php

namespace App\RepublicanSilence\TagExtractor;

use App\Address\Address;
use App\Entity\Adherent;
use App\Entity\CommitteeMembership;

class CommitteeReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        if (null === $slug) {
            return [];
        }

        /** @var CommitteeMembership $membership */
        foreach ($adherent->getMemberships()->getCommitteeHostMemberships() as $membership) {
            $committee = $membership->getCommittee();
            if ($committee->getSlug() === $slug) {
                $codes = $committee->getReferentTagsCodes();
                if (Address::FRANCE === $committee->getCountry()) {
                    $codes[] = Address::FRANCE;
                }

                return $codes;
            }
        }

        return [];
    }
}
