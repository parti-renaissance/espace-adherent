<?php

namespace AppBundle\RepublicanSilence\TagExtractor;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeMembership;

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
