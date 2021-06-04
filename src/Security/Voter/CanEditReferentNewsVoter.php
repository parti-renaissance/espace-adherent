<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\Geo\ZoneRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CanEditReferentNewsVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_REFERENT_JECOUTE_NEWS';

    private $session;
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

        return $adherent->isReferent()
            && \in_array($subject->getZone(), $this->zoneRepository->findForJecouteByReferentTags($adherent->getManagedArea()->getTags()->toArray()), true);
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof News;
    }
}
