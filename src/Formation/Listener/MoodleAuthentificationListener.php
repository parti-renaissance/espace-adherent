<?php

declare(strict_types=1);

namespace App\Formation\Listener;

use App\Formation\Moodle\UserManager;
use App\OAuth\Model\Scope;
use League\OAuth2\Server\RequestAccessTokenEvent;
use League\OAuth2\Server\RequestEvent;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MoodleAuthentificationListener implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly UserManager $userManager, LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [RequestEvent::ACCESS_TOKEN_ISSUED => 'onRequestAccessToken'];
    }

    public function onRequestAccessToken(RequestAccessTokenEvent $event): void
    {
        $accessToken = $event->getAccessToken();

        if (!\in_array(Scope::FORMATION, $accessToken->getScopes())) {
            return;
        }

        if (!$userUuid = $accessToken->getUserIdentifier()) {
            return;
        }

        try {
            $this->userManager->updateUser($userUuid);
        } catch (\Throwable $exception) {
            $this->logger->error('Moodle user update failed for {uuid}: {message}', [
                'uuid' => $userUuid,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
