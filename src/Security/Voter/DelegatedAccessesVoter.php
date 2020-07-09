<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DelegatedAccessesVoter extends Voter
{
    private const HAS_DELEGATED_ACCESS_ANY = 'HAS_DELEGATED_ACCESS_ANY';
    private const HAS_DELEGATED_ACCESS_EVENTS = 'HAS_DELEGATED_ACCESS_EVENTS';
    private const HAS_DELEGATED_ACCESS_ADHERENTS = 'HAS_DELEGATED_ACCESS_ADHERENTS';
    private const HAS_DELEGATED_ACCESS_COMMITTEE = 'HAS_DELEGATED_ACCESS_COMMITTEE';
    private const HAS_DELEGATED_ACCESS_MESSAGES = 'HAS_DELEGATED_ACCESS_MESSAGES';
    private const HAS_DELEGATED_ACCESS_JECOUTE = 'HAS_DELEGATED_ACCESS_JECOUTE';
    private const HAS_DELEGATED_ACCESS_CITIZEN_PROJECTS = 'HAS_DELEGATED_ACCESS_CITIZEN_PROJECTS';
    private const HAS_DELEGATED_ACCESS_ELECTED_REPRESENTATIVES = 'HAS_DELEGATED_ACCESS_ELECTED_REPRESENTATIVES';

    /** @var SessionInterface */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    protected function supports($attribute, $subject)
    {
        return 0 === \strpos($attribute, 'HAS_DELEGATED_ACCESS_');
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof Adherent) {
            return false;
        }

        $delegatedAccess = $user->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY));

        if (null === $delegatedAccess) {
            return false;
        }

        switch ($attribute) {
            case self::HAS_DELEGATED_ACCESS_ANY:
                return \count($delegatedAccess->getAccesses()) > 0;
            case self::HAS_DELEGATED_ACCESS_ADHERENTS:
                return \in_array(DelegatedAccess::ACCESS_ADHERENTS, $delegatedAccess->getAccesses(), true);
            case self::HAS_DELEGATED_ACCESS_EVENTS:
                return \in_array(DelegatedAccess::ACCESS_EVENTS, $delegatedAccess->getAccesses(), true);
            case self::HAS_DELEGATED_ACCESS_COMMITTEE:
                return \in_array(DelegatedAccess::ACCESS_COMMITTEE, $delegatedAccess->getAccesses(), true);
            case self::HAS_DELEGATED_ACCESS_MESSAGES:
                return \in_array(DelegatedAccess::ACCESS_MESSAGES, $delegatedAccess->getAccesses(), true);
            case self::HAS_DELEGATED_ACCESS_JECOUTE:
                return \in_array(DelegatedAccess::ACCESS_JECOUTE, $delegatedAccess->getAccesses(), true);
            case self::HAS_DELEGATED_ACCESS_CITIZEN_PROJECTS:
                return \in_array(DelegatedAccess::ACCESS_CITIZEN_PROJECTS, $delegatedAccess->getAccesses(), true);
            case self::HAS_DELEGATED_ACCESS_ELECTED_REPRESENTATIVES:
                return \in_array(DelegatedAccess::ACCESS_ELECTED_REPRESENTATIVES, $delegatedAccess->getAccesses(), true);
            default:
                return false;
        }
    }
}
