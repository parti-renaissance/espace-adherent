<?php

namespace App\Normalizer\Indexer;

use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
use App\Entity\Team\Member;

class PhoningCampaignNormalizer extends AbstractJeMengageTimelineFeedNormalizer
{
    protected function getClassName(): string
    {
        return Campaign::class;
    }

    /** @param Campaign $object */
    protected function getTitle(object $object): string
    {
        return $object->getTitle();
    }

    /** @param Campaign $object */
    protected function getDescription(object $object): ?string
    {
        return \sprintf('Vous avez jusqu\'au %s pour remplir %s questionnaires.',
            $this->formatDate($object->getFinishAt()),
            $object->getGoal()
        );
    }

    /** @param Campaign $object */
    protected function isNational(object $object): bool
    {
        return $object->isNationalVisibility();
    }

    /** @param Campaign $object */
    protected function getDate(object $object): ?\DateTimeInterface
    {
        return $object->getCreatedAt();
    }

    /** @param Campaign $object */
    protected function getFinishAt(object $object): ?\DateTimeInterface
    {
        return $object->getFinishAt();
    }

    /** @param Campaign $object */
    protected function getAuthorObject(object $object): ?Adherent
    {
        return $object->getCreatedByAdherent();
    }

    /** @param Campaign $object */
    protected function getAdherentIds(object $object): ?array
    {
        return $object->getTeam()
             ? array_values(array_unique(array_filter(array_map(function (Member $member): ?int {
                 return $member->getAdherent() ? $member->getAdherent()->getId() : null;
             }, $object->getTeam()->getMembers()->toArray()))))
             : null;
    }

    /** @param Campaign $object */
    protected function getZoneCodes(object $object): ?array
    {
        return $this->buildZoneCodes(!$object->isNationalVisibility() ? $object->getZone() : null);
    }
}
