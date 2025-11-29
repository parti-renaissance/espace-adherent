<?php

declare(strict_types=1);

namespace App\AdherentMessage\Sender;

use App\Entity\AdherentMessage\AdherentMessageInterface;

interface SenderInterface
{
    public function supports(AdherentMessageInterface $message, bool $forTest): bool;

    public function send(AdherentMessageInterface $message, array $recipients = []): void;

    public function sendTest(AdherentMessageInterface $message, array $recipients = []): bool;
}
