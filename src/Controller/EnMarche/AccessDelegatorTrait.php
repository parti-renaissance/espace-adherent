<?php

namespace App\Controller\EnMarche;

use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

trait AccessDelegatorTrait
{
    protected function getMainUser(SessionInterface $session): UserInterface
    {
        $user = $this->getUser();

        if (null !== $delegatedAccessUuid = $session->get(DelegatedAccess::ATTRIBUTE_KEY)) {
            return $user->getReceivedDelegatedAccessByUuid($delegatedAccessUuid)->getDelegator();
        }

        return $user;
    }
}
