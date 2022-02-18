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

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            return false;
        }

        $scopeCode = $scope ? ($scope->getDelegatorCode() ?? $scope->getCode()) : null;

        return null === $subject || $subject === $scopeCode;
    }

    protected function supports($attribute, $subject)
    {
        return
            self::PERMISSION === $attribute
            && $this->requestStack->getMasterRequest()
        ;
    }
}
