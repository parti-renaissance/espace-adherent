<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\AdherentMessage\AdherentMessageSynchronizedObjectInterface;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;

interface AdherentMessageFilterInterface extends AdherentMessageSynchronizedObjectInterface
{
    public function getMessage(): AdherentMessageInterface;

    public function setMessage(AdherentMessageInterface $message): void;
}
