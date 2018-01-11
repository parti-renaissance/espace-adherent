<?php

namespace AppBundle\Producer;

use OldSound\RabbitMqBundle\RabbitMq\Producer;

class CitizenProjectSummaryProducer extends Producer
{
    public function scheduleBroadcast(string $uuid, string $approvedSince): void
    {
        $this->publish(\GuzzleHttp\json_encode([
            'adherent_uuid' => $uuid,
            'approved_since' => $approvedSince,
        ]));
    }
}
