<?php

namespace App\AdherentMessage\Sender;

use App\Entity\AdherentMessage\AdherentMessageInterface;

interface SenderInterface
{
    public function supports(AdherentMessageInterface $message): bool;

    public function send(AdherentMessageInterface $message, array $recipients = []): bool;

    public function sendTest(AdherentMessageInterface $message, array $recipients = []): bool;
}
