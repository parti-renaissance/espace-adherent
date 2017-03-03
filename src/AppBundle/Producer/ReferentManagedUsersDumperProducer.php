<?php

namespace AppBundle\Producer;

use AppBundle\Entity\Adherent;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class ReferentManagedUsersDumperProducer extends Producer
{
    public function scheduleDump(Adherent $referent, string $type)
    {
        $this->publish(json_encode([
            'referent_uuid' => $referent->getUuid()->toString(),
            'referent_email' => $referent->getEmailAddress(),
            'type' => $type,
        ]));
    }
}
