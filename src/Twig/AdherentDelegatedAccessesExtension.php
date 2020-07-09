<?php

namespace App\Twig;

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
        $user = $this->security->getUser();

        $delegatedAccess = $user->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY));

        if ($delegatedAccess) {
            return $delegatedAccess->getDelegator();
        }

        return $user;
    }

    public function getDelegatedAccess(): ?DelegatedAccess
    {
        $user = $this->security->getUser();

        return $user->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY));
    }
}
