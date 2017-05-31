<?php

namespace AppBundle\Logging;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class CurrentUserProcessor
{
    private $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    public function processRecord(array $record)
    {
        $tokenStorage = $this->locator->get('security.token_storage');
        $authorizationChecker = $this->locator->get('security.authorization_checker');

        $token = $tokenStorage->getToken();
        $record['extra']['user'] = 'anonymous';

        if (null !== $token && $authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            $record['extra']['user'] = $token->getUsername();
        }

        return $record;
    }
}
