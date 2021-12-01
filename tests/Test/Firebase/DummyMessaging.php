<?php

namespace Tests\App\Test\Firebase;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\MulticastSendReport;

class DummyMessaging extends Messaging
{
    public function __construct()
    {
    }

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
}
