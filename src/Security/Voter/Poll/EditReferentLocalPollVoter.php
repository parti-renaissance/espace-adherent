<?php

namespace App\Security\Voter\Poll;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\Poll\LocalPoll;
use App\Repository\Geo\ZoneRepository;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EditReferentLocalPollVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_REFERENT_LOCAL_POLL';

    /** @var SessionInterface */
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
        return self::PERMISSION === $attribute && $subject instanceof LocalPoll;
    }
}
