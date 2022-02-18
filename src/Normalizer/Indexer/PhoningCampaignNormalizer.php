<?php

namespace App\Normalizer\Indexer;

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
        return sprintf('Vous avez jusqu\'au %s pour remplir %s questionnaires.',
            $this->formatDate($object->getFinishAt()),
            $object->getGoal()
        );
    }

    /** @param Campaign $object */
    protected function isLocal(object $object): bool
    {
        return !$object->isNationalVisibility();
    }

    /** @param Campaign $object */
    protected function isNational(object $object): bool
    {
        return $object->isNationalVisibility();
    }

    /** @param Campaign $object */
    protected function getDate(object $object): ?\DateTime
    {
        return $object->getCreatedAt();
    }

    /** @param Campaign $object */
    protected function getFinishAt(object $object): ?\DateTime
    {
        return $object->getFinishAt();
    }

    /** @param Campaign $object */
    protected function getAuthor(object $object): ?string
    {
        return $object->getCreatedByAdherent() ? $object->getCreatedByAdherent()->getFullName() : null;
    }

    /** @param Campaign $object */
    protected function getAdherentIds(object $object): ?array
    {
        return $object->getTeam()
             ? array_values(array_unique(array_filter(array_map(function (Member $member): ?int {
                 return $member->getAdherent() ? $member->getAdherent()->getId() : null;
             }, $object->getTeam()->getMembers()->toArray()))))
             : null
         ;
    }
}
