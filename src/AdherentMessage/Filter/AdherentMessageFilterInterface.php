<?php

namespace App\AdherentMessage\Filter;

use App\AdherentMessage\AdherentMessageSynchronizedObjectInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;

interface AdherentMessageFilterInterface extends AdherentMessageSynchronizedObjectInterface
{
    public function getMessage(): AdherentMessageInterface;

    public function setMessage(AdherentMessageInterface $message): void;
}
