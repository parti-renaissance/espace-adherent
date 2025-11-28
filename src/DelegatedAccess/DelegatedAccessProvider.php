<?php

declare(strict_types=1);

namespace App\DelegatedAccess;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\RequestStack;

class DelegatedAccessProvider
{
    public function __construct(private readonly RequestStack $requestStack)
    {
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
        if (null !== $delegatedAccessUuid = $this->requestStack->getSession()->get(DelegatedAccess::ATTRIBUTE_KEY)) {
            return $user->getReceivedDelegatedAccessByUuid($delegatedAccessUuid);
        }

        return null;
    }
}
