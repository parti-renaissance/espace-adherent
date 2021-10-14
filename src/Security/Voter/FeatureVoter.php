<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Scope\AuthorizationChecker;
use App\Scope\Exception\ScopeExceptionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FeatureVoter extends Voter
{
    /** @var AuthorizationChecker */
    private $authorizationChecker;
    /** @var RequestStack */
    private $requestStack;

    public function __construct(AuthorizationChecker $authorizationChecker, RequestStack $requestStack)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
    }

    protected function supports($attribute, $subject)
    {
        return 0 === strpos($attribute, 'HAS_FEATURE_') && ($request = $this->requestStack->getMasterRequest());
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof Adherent) {
            return false;
        }

        try {
            return $this->authorizationChecker->isFeatureGranted(
                $this->requestStack->getMasterRequest(),
                $user,
                mb_strtolower(str_replace('HAS_FEATURE_', '', $attribute))
            );
        } catch (ScopeExceptionInterface $exception) {
            return false;
        }
    }
}
