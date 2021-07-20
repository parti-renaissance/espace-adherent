<?php

namespace App\Security\Voter\Audience;

use App\Audience\AudienceHelper;
use App\Entity\Adherent;
use App\Entity\Audience\AudienceInterface;
use App\Entity\MyTeam\DelegatedAccess;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CreateAudienceVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_CREATE_AUDIENCE';

    /** @var SessionInterface */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

        return AudienceHelper::validateAdherentAccess($adherent, \get_class($subject));
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof AudienceInterface;
    }
}
