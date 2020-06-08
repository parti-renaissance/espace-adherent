<?php

namespace App\Twig;

use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdherentDelegatedAccessesExtension extends AbstractExtension
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_user', [$this, 'getUser']),
            new TwigFunction('get_first_access_route', [$this, 'getFirstAccessRoute']),
        ];
    }

    public function getUser(?DelegatedAccess $delegatedAccess)
    {
        if ($delegatedAccess) {
            return $delegatedAccess->getDelegator();
        }

        return $this->tokenStorage->getToken()->getUser();
    }

    public function getFirstAccessRoute(array $delegatedAccesses, string $type)
    {
        $delegatedAccess = null;
        foreach ($delegatedAccesses as $access) {
            if ($access->getType() === $type) {
                $delegatedAccess = $access;
                break;
            }
        }

        if (!$delegatedAccess) {
            throw new \LogicException('No delegated access found. Unable to find route');
        }

        $routes = [
            DelegatedAccess::ACCESS_MESSAGES => "app_message_{$type}_delegated_list",
            DelegatedAccess::ACCESS_EVENTS => "app_{$type}_event_manager_delegated_events",
            DelegatedAccess::ACCESS_ADHERENTS => "app_{$type}_managed_users_delegated_list",
            DelegatedAccess::ACCESS_COMMITTEE => "app_{$type}_delegated_committee",
        ];

        return $routes[$delegatedAccess->getAccesses()[0]];
    }
}
