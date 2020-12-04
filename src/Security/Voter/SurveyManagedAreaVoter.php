<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\Geo\ZoneRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SurveyManagedAreaVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'IS_SURVEY_MANAGER_OF';

    /** @var SessionInterface */
    private $session;
    /** @var ZoneRepository */
    private $zoneRepository;

    public function __construct(SessionInterface $session, ZoneRepository $zoneRepository)
    {
        $this->session = $session;
        $this->zoneRepository = $zoneRepository;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        /** @var LocalSurvey $subject */
        if ($adherent->isJecouteManager()) {
            return $subject->getZone() === $adherent->getJecouteManagedArea()->getZone();
        }

        if ($adherent->isReferent()) {
            $zones = $this->zoneRepository->findForJecouteByReferentTags($adherent->getManagedArea()->getTags()->toArray());

            return \in_array($subject->getZone(), $zones);
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof LocalSurvey;
    }
}
