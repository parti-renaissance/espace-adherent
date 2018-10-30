<?php

namespace Tests\AppBundle\Test\Producer;

use AppBundle\Deputy\DeputyMessage;
use AppBundle\Producer\DeputyMessageDispatcherProducerInterface;

class DeputyMessageNullProducer implements DeputyMessageDispatcherProducerInterface
{
    public function scheduleDispatch(DeputyMessage $message): void
    {
    }
}
