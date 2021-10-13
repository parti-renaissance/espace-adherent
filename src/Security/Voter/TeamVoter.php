<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Scope\AuthorizationChecker;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\FeatureEnum;
use Symfony\Component\HttpFoundation\RequestStack;

class TeamVoter extends AbstractAdherentVoter
{
    public const HAS_FEATURE_TEAM = 'HAS_FEATURE_TEAM';
    private RequestStack  $requestStack;
    private AuthorizationChecker $authorizationChecker;

    public function __construct(RequestStack $requestStack, AuthorizationChecker $authorizationChecker)
    {
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        try {
            return $this->authorizationChecker->isFeatureGranted($this->requestStack->getMasterRequest(), $adherent, FeatureEnum::TEAM);
        } catch (ScopeExceptionInterface $exception) {
            return false;
        }
    }

    protected function supports($attribute, $subject)
    {
        return self::HAS_FEATURE_TEAM === $attribute;
    }
}
