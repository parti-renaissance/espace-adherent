<?php

namespace AppBundle\RepublicanSilence\TagExtractor;

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
                return $committee->getReferentTagsCodes();
            }
        }

        return [];
    }
}
