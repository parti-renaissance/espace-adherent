<?php

namespace App\Security\Voter;

use App\Scope\AuthorizationChecker;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ScopeFeatureVoter extends Voter
{
    public const SCOPE_AND_FEATURE_GRANTED = 'REQUEST_SCOPE_GRANTED';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly Security $security,
    ) {
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            try {
                return $this->authorizationChecker->isFeatureGranted($this->requestStack->getMainRequest(), $this->security->getUser(), \is_array($subject) ? $subject : [$subject]);
            } catch (\Throwable) {
                return false;
            }
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

        return $scope->containsFeatures($subject);
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::SCOPE_AND_FEATURE_GRANTED === $attribute && $this->requestStack->getMainRequest();
    }
}
