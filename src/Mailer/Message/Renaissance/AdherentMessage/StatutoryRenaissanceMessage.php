<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessageInterface;

final class StatutoryRenaissanceMessage extends AbstractRenaissanceAdherentMessage
{
    public static function create(AdherentMessageInterface $adherentMessage, array $adherents): self
    {
        $message = parent::create($adherentMessage, $adherents);
        $message->setSenderEmail('contact@parti-renaissance.fr');

        return $message;
    }
}
