<?php

namespace App\Twig;

use App\Controller\AccessDelegatorTrait;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdherentDelegatedAccessesExtension extends AbstractExtension
{
    use AccessDelegatorTrait;

    /** @var Security */
    private $security;

    /** @var RouterInterface */
    private $router;

    public function __construct(Security $security, RouterInterface $router)
    {
        $this->security = $security;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('current_user', [$this, 'getCurrentUser']),
            new TwigFunction('get_delegated_access', [$this, 'getDelegatedAccess']),
            new TwigFunction('get_first_access_route', [$this, 'getFirstAccessRoute']),
            new TwigFunction('get_path', [$this, 'getPath']),
        ];
    }

    public function getCurrentUser(Request $request): UserInterface
    {
        $user = $this->security->getUser();

        $delegatedAccess = $user->getReceivedDelegatedAccessByUuid($request->attributes->get(DelegatedAccess::ATTRIBUTE_KEY));

        if ($delegatedAccess) {
            return $delegatedAccess->getDelegator();
        }

        return $user;
    }

    public function getDelegatedAccess(Request $request)
    {
        $user = $this->security->getUser();

        return $user->getReceivedDelegatedAccessByUuid($request->attributes->get(DelegatedAccess::ATTRIBUTE_KEY));
    }

    public function getFirstAccessRoute(DelegatedAccess $delegatedAccess): string
    {
        $routes = [
            DelegatedAccess::ACCESS_MESSAGES => "app_message_{$delegatedAccess->getType()}_delegated_list",
            DelegatedAccess::ACCESS_EVENTS => "app_{$delegatedAccess->getType()}_event_manager_delegated_events",
            DelegatedAccess::ACCESS_ADHERENTS => "app_{$delegatedAccess->getType()}_managed_users_delegated_list",
            DelegatedAccess::ACCESS_COMMITTEE => "app_{$delegatedAccess->getType()}_delegated_committee",
        ];

        return $routes[$delegatedAccess->getAccesses()[0]];
    }

    public function getPath(
        string $spaceName,
        ?string $type,
        string $action,
        Request $request,
        array $routeParams = []
    ): string {
        $delegatedAccess = $this->security->getUser()->getReceivedDelegatedAccessByUuid($request->attributes->get(DelegatedAccess::ATTRIBUTE_KEY));

        // route is usually "app_{space}_{type}_...", but some are "app_{type}_{space}_..."
        if (\in_array($type, ['message', 'jecoute'])) {
            $route = sprintf(
                'app_%s_%s%s_%s',
                $type,
                $spaceName,
                $delegatedAccess ? '_delegated' : '',
                $action
            );
        } else {
            $route = sprintf(
                'app_%s%s%s_%s',
                $spaceName,
                $type ? '_'.$type : '',
                $delegatedAccess ? '_delegated' : '',
                $action
            );
        }

        return $this->router->generate($route, \array_merge($routeParams, $delegatedAccess ? [DelegatedAccess::ATTRIBUTE_KEY => $delegatedAccess->getUuid()] : []));
    }
}
