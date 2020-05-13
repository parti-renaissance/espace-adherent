<?php

namespace Tests\App\Test\Producer;

use App\Deputy\DeputyMessage;
use App\Producer\DeputyMessageDispatcherProducerInterface;

class DeputyMessageNullProducer implements DeputyMessageDispatcherProducerInterface
{
    public function scheduleDispatch(DeputyMessage $message): void
    {
    }
}
