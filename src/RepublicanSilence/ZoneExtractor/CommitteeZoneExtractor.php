<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;
use App\Entity\CommitteeMembership;

class CommitteeZoneExtractor implements ZoneExtractorInterface
{
    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        if (null === $slug) {
            return [];
        }

        /** @var CommitteeMembership $membership */
        foreach ($adherent->getMemberships()->getCommitteeHostMemberships() as $membership) {
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
