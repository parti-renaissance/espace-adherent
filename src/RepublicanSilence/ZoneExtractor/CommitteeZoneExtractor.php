<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;

class CommitteeZoneExtractor implements ZoneExtractorInterface
{
    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        if (null === $slug) {
            return [];
        }

        if ($membership = $adherent->getCommitteeMembership()) {
            $committee = $membership->getCommittee();
            if ($committee->getSlug() === $slug) {
                return $committee->getZones()->toArray();
            }
        }

        return [];
    }

    public function supports(int $type): bool
    {
        return ZoneExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR === $type;
    }
}
