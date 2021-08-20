<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Scope\AuthorizationChecker;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestScopeVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'REQUEST_SCOPE_GRANTED';

    private $requestStack;
    private $authorizationChecker;

    public function __construct(RequestStack $requestStack, AuthorizationChecker $authorizationChecker)
    {
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$subject || !\is_string($subject)) {
            $request = $this->requestStack->getMasterRequest();

            if (!$subject = $this->authorizationChecker->getScope($request)) {
                return false;
            }
        }

        return $this->authorizationChecker->isScopeGranted($subject, $adherent);
    }

    protected function supports($attribute, $subject)
    {
        return
            self::PERMISSION === $attribute
            && $this->requestStack->getMasterRequest()
        ;
    }
}
