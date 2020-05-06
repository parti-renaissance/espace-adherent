<?php

namespace App\Producer;

use App\Deputy\DeputyMessage;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class DeputyMessageDispatcherProducer extends Producer implements DeputyMessageDispatcherProducerInterface
{
    public function scheduleDispatch(DeputyMessage $message): void
    {
        $this->publish(\GuzzleHttp\json_encode([
            'uuid' => $message->getUuid()->toString(),
        ]));
    }
}
