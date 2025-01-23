<?php

namespace App\Security\Voter;

use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RequestScopeVoter extends Voter
{
    public const SCOPE_AND_FEATURE_GRANTED = 'REQUEST_SCOPE_GRANTED';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return false;
        }

        if (!$subject) {
            return true;
        }

        if ($subject === $scope->getMainCode()) {
            return true;
        }

        if (!\is_array($subject)) {
            $subject = [$subject];
        }

        foreach ($subject as $feature) {
            if ($scope->hasFeature($feature)) {
                return true;
            }
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::SCOPE_AND_FEATURE_GRANTED === $attribute && $this->requestStack->getMainRequest();
    }
}
