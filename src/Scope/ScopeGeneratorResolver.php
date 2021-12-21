<?php

namespace App\Scope;

use App\Entity\Adherent;
use App\Scope\Generator\ScopeGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class ScopeGeneratorResolver
{
    private RequestStack $requestStack;
    private Security $security;
    private AuthorizationChecker $authorizationChecker;

    public function __construct(
        RequestStack $requestStack,
        Security $security,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function resolve(): ?ScopeGeneratorInterface
    {
        return $this->authorizationChecker->getScopeGenerator(
            $this->getRequest(),
            $this->getAdherent()
        );
    }

    private function getAdherent(): Adherent
    {
        return $this->security->getUser();
    }

    private function getRequest(): Request
    {
        return $this->requestStack->getMasterRequest();
    }
}
