<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Scope\AuthorizationChecker;
use App\Scope\Exception\ScopeExceptionInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FeatureVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'IS_FEATURE_GRANTED';

    private AuthorizationChecker $authorizationChecker;
    private RequestStack $requestStack;

    public function __construct(AuthorizationChecker $authorizationChecker, RequestStack $requestStack)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
    }

    protected function supports($attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $this->requestStack->getMasterRequest();
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $features = \is_array($subject) ? $subject : [$subject];

        try {
            return $this->authorizationChecker->isFeatureGranted(
                $this->requestStack->getMasterRequest(),
                $adherent,
                $features
            );
        } catch (ScopeExceptionInterface $exception) {
            return false;
        }
    }
}
