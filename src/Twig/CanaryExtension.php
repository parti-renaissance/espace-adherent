<?php

namespace App\Twig;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CanaryExtension extends AbstractExtension
{
    private $authorizationChecker;
    private $canaryMode;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, bool $canaryMode)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->canaryMode = $canaryMode;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('is_canary_enabled', [$this, 'isCanaryEnabled']),
        ];
    }

    public function isCanaryEnabled(): bool
    {
        if ($this->canaryMode) {
            return true;
        }

        try {
            return $this->authorizationChecker->isGranted('ROLE_CANARY_TESTER');
        } catch (AuthenticationException $e) {
        }

        return false;
    }
}
