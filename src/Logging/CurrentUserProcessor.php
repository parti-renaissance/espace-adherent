<?php

namespace AppBundle\Logging;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class CurrentUserProcessor
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function processRecord(array $record)
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $authorizationChecker = $this->container->get('security.authorization_checker');

        $token = $tokenStorage->getToken();
        $record['extra']['user'] = 'anonymous';

        if (null !== $token && $authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)) {
            $record['extra']['user'] = $token->getUsername();
        }

        return $record;
    }
}
