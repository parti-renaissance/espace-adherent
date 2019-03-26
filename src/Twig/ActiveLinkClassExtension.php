<?php

namespace AppBundle\Twig;

use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ActiveLinkClassExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_active_route', [$this, 'isActiveRoute']),
        ];
    }

    /**
     * @param string|string[]|array $routeName
     */
    public function isActiveRoute(Request $request, $routeName): bool
    {
        $currentRouteName = $request->attributes->get('_route');

        if (\is_array($routeName)) {
            return \in_array($currentRouteName, $routeName, true);
        }

        if ('*' === substr($routeName, -1)) {
            return false !== strpos($currentRouteName, rtrim($routeName, '*'));
        }

        return $currentRouteName === $routeName;
    }
}
