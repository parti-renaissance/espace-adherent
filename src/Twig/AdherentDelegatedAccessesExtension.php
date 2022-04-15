<?php

namespace App\Twig;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdherentDelegatedAccessesExtension extends AbstractExtension
{
    /** @var Security */
    private $security;

    /** @var SessionInterface */
    private $session;

    public function __construct(Security $security, SessionInterface $session)
    {
        $this->security = $security;
        $this->session = $session;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('current_user', [$this, 'getCurrentUser']),
            new TwigFunction('get_delegated_access', [$this, 'getDelegatedAccess']),
        ];
    }

    public function getCurrentUser(): UserInterface
    {
        if ($delegatedAccess = $this->getDelegatedAccess()) {
            return $delegatedAccess->getDelegator();
        }

        return $this->security->getUser();
    }

    public function getDelegatedAccess(): ?DelegatedAccess
    {
        /** @var Adherent $user */
        $user = $this->security->getUser();

        return $user->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY));
    }
}
