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
        $zones = $this->zoneRepository->findForJecouteByReferentTags($adherent->getManagedArea()->getTags()->toArray());

        return $adherent->isReferent() && \in_array($subject->getZone(), $zones, true);
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof News;
    }
}
