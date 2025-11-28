<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdherentDelegatedAccessesExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
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

        return $user->getReceivedDelegatedAccessByUuid($this->requestStack->getSession()->get(DelegatedAccess::ATTRIBUTE_KEY));
    }
}
