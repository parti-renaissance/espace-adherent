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

        foreach ((array) $routeName as $route) {
            if ($this->match($currentRouteName, $route)) {
                return true;
            }
        }

        return false;
    }

    private function match(string $currentRoute, string $expectedRoute): bool
    {
        if ('*' === substr($expectedRoute, -1)) {
            return false !== strpos($currentRoute, rtrim($expectedRoute, '*'));
        }

        return $currentRoute === $expectedRoute;
    }
}
