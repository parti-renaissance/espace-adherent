<?php

namespace App\Security\Voter;

use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RequestScopeVoter extends Voter
{
    public const PERMISSION = 'REQUEST_SCOPE_GRANTED';

    private RequestStack $requestStack;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(RequestStack $requestStack, ScopeGeneratorResolver $scopeGeneratorResolver)
    {
        $this->requestStack = $requestStack;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            return false;
        }

        $scopeCode = $scope ? $scope->getMainCode() : null;

        return null === $subject || $subject === $scopeCode;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return
            self::PERMISSION === $attribute
            && $this->requestStack->getMainRequest();
    }
}
