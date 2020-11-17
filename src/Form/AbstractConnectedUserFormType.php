<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\ReferentTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractConnectedUserFormType extends AbstractType
{
    private $security;
    private $session;

    public function __construct(Security $security, SessionInterface $session)
    {
        $this->security = $security;
        $this->session = $session;
    }

    protected function getUser(): ?Adherent
    {
        $user = $this->security->getUser();

        if (!$user || !$user instanceof Adherent) {
            return null;
        }

        $delegatedAccess = $user->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY));

        if ($delegatedAccess) {
            return $delegatedAccess->getDelegator();
        }

        return $user;
    }

    /**
     * @return ReferentTag[]
     */
    protected function getReferentTags(): array
    {
        if (!$user = $this->getUser()) {
            return [];
        }

        if (!$user->isReferent()) {
            return [];
        }

        return $user->getManagedArea()->getTags()->toArray();
    }

    protected function getReferentZones(): array
    {
        $zones = [];

        foreach ($this->getReferentTags() as $referentTag) {
            $zones[] = $referentTag->getZone();
        }

        return $zones;
    }
}
