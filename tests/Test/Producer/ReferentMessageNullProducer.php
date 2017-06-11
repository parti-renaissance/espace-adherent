<?php

namespace Tests\AppBundle\Test\Producer;

use AppBundle\Producer\ReferentMessageDispatcherProducerInterface;
use AppBundle\Referent\ReferentMessage;

class ReferentMessageNullProducer implements ReferentMessageDispatcherProducerInterface
{
    public function scheduleDispatch(ReferentMessage $message): void
    {
    }
}
