<?php

namespace App\Scope;

use App\Entity\Adherent;
use App\Scope\Generator\ScopeGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ScopeGeneratorResolver
{
    private ?Scope $currentScope = null;
    private ?ScopeGeneratorInterface $currentScopeGenerator = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security,
        private readonly AuthorizationChecker $authorizationChecker,
    ) {
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
