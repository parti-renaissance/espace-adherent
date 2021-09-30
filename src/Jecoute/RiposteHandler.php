<?php

namespace App\Jecoute;

use App\Entity\Jecoute\Riposte;
use App\JeMarche\JeMarcheDeviceNotifier;

class RiposteHandler
{
    private $deviceNotifier;

    public function __construct(JeMarcheDeviceNotifier $deviceNotifier)
    {
        $this->deviceNotifier = $deviceNotifier;
    }

    public function handleNotification(Riposte $riposte): void
    {
        if (!$riposte->isWithNotification()) {
            return;
        }

        $this->deviceNotifier->sendRiposteNotification($riposte);
    }
}
