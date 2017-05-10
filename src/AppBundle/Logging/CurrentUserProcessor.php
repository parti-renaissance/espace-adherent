<?php

namespace AppBundle\Logging;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class CurrentUserProcessor
{
    private $tokenStorage;
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function processRecord(array $record)
    {
        $token = $this->tokenStorage->getToken();
        $record['extra']['user'] = 'anonymous';

        if (null !== $token && $this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            $record['extra']['user'] = $token->getUsername();
        }

        return $record;
    }
}
