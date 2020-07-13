<?php

namespace App\Controller\EnMarche;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

trait AccessDelegatorTrait
{
    protected function getMainUser(SessionInterface $session): UserInterface
    {
        $user = $this->getUser();

        if (null !== $delegatedAccess = $this->getDelegatedAccess($user, $session)) {
            return $delegatedAccess->getDelegator();
        }

        return $user;
    }

    protected function getRestrictedCommittees(SessionInterface $session)
    {
        if (null !== $delegatedAccess = $this->getDelegatedAccess($this->getUser(), $session)) {
            return $delegatedAccess->getRestrictedCommittees()->map(static function (Committee $committee) {
                return $committee->getUuidAsString();
            })->toArray();
        }

        return [];
    }

    protected function getRestrictedCities(SessionInterface $session)
    {
        if (null !== $delegatedAccess = $this->getDelegatedAccess($this->getUser(), $session)) {
            return $delegatedAccess->getRestrictedCities();
        }

        return [];
    }

    protected function getDelegatedAccess(Adherent $user, SessionInterface $session): ?DelegatedAccess
    {
        if (null !== $delegatedAccessUuid = $session->get(DelegatedAccess::ATTRIBUTE_KEY)) {
            return $user->getReceivedDelegatedAccessByUuid($delegatedAccessUuid);
        }

        return null;
    }
}
