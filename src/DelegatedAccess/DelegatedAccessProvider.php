<?php

namespace App\DelegatedAccess;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DelegatedAccessProvider
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getMainUser(Adherent $user): Adherent
    {
        if (null !== $delegatedAccess = $this->getDelegatedAccess($user)) {
            return $delegatedAccess->getDelegator();
        }

        return $user;
    }

    private function getDelegatedAccess(Adherent $user): ?DelegatedAccess
    {
        if (null !== $delegatedAccessUuid = $this->session->get(DelegatedAccess::ATTRIBUTE_KEY)) {
            return $user->getReceivedDelegatedAccessByUuid($delegatedAccessUuid);
        }

        return null;
    }
}
