<?php

namespace AppBundle\Producer;

use AppBundle\Referent\ReferentMessage;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class ReferentMessageDispatcherProducer extends Producer implements ReferentMessageDispatcherProducerInterface
{
    public function scheduleDispatch(ReferentMessage $message): void
    {
        $this->publish(\GuzzleHttp\json_encode([
            'uuid' => $message->getUuid()->toString(),
        ]));
    }
}
