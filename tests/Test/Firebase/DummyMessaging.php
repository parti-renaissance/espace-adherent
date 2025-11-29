<?php

declare(strict_types=1);

namespace Tests\App\Test\Firebase;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\AppInstance;
use Kreait\Firebase\Messaging\MulticastSendReport;

class DummyMessaging implements Messaging
{
    public function send($message, bool $validateOnly = false): array
    {
        return [];
    }

    public function sendMulticast($message, $registrationTokens, bool $validateOnly = false): MulticastSendReport
    {
        return MulticastSendReport::withItems([]);
    }

    public function unsubscribeFromAllTopics($registrationTokenOrTokens): array
    {
        return [];
    }

    public function subscribeToTopics(iterable $topics, $registrationTokenOrTokens): array
    {
        return [];
    }

    public function sendAll($messages, bool $validateOnly = false): MulticastSendReport
    {
        return MulticastSendReport::withItems([]);
    }

    public function validate($message): array
    {
        return [];
    }

    public function validateRegistrationTokens($registrationTokenOrTokens): array
    {
        return [];
    }

    public function subscribeToTopic($topic, $registrationTokenOrTokens): array
    {
        return [];
    }

    public function unsubscribeFromTopic($topic, $registrationTokenOrTokens): array
    {
        return [];
    }

    public function unsubscribeFromTopics(array $topics, $registrationTokenOrTokens): array
    {
        return [];
    }

    public function getAppInstance($registrationToken): AppInstance
    {
        throw NotFound::becauseTokenNotFound('');
    }
}
