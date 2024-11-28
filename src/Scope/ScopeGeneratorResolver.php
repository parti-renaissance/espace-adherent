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

    private ?Scope $currentScope = null;
    private ?ScopeGeneratorInterface $currentScopeGenerator = null;

    public function __construct(
        RequestStack $requestStack,
        Security $security,
        AuthorizationChecker $authorizationChecker,
    ) {
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function resolve(): ?ScopeGeneratorInterface
    {
        if ($this->currentScopeGenerator) {
            return $this->currentScopeGenerator;
        }

        $currentUser = $this->getCurrentUser();

        if (!$currentUser) {
            return null;
        }

        return $this->currentScopeGenerator = $this->authorizationChecker->getScopeGenerator(
            $this->getRequest(),
            $currentUser
        );
    }

    public function generate(): ?Scope
    {
        if ($this->currentScope) {
            return $this->currentScope;
        }

        $scopeGenerator = $this->resolve();

        return $this->currentScope = $scopeGenerator?->generate($this->getCurrentUser());
    }

    private function getCurrentUser(): ?Adherent
    {
        $adherent = $this->security->getUser();

        return $adherent instanceof Adherent ? $adherent : null;
    }

    private function getRequest(): Request
    {
        return $this->requestStack->getMainRequest();
    }
}
