<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Repository\Geo\ZoneRepository;

class ReferentNewsPublicationVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'IS_ALLOWED_TO_PUBLISH_JECOUTE_NEWS';

    private $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->isReferent()
            && \in_array($subject->getZone(), $this->zoneRepository->findForJecouteByReferentTags($adherent->getManagedArea()->getTags()->toArray()), true);
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof News;
    }
}
