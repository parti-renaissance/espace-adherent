<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\Event\Event;
use Ramsey\Uuid\Uuid;

class AgoraEventInvitationMessage extends AbstractRenaissanceMessage
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(Event $event, Agora $agora, array $adherents, string $eventLink): self
    {
        $first = current($adherents);

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            'Invitation à un événement',
            [
                'event_name' => self::escape($event->getName()),
                'event_organiser' => self::escape($event->getOrganizerName()),
                'event_date' => static::formatDate($event->getLocalBeginAt(), 'EEEE d MMMM y'),
                'event_hour' => static::formatDate($event->getLocalBeginAt(), 'HH\'h\'mm'),
                'event_link' => $eventLink,
                'visio_url' => $event->getVisioUrl(),
                'live_url' => $event->liveUrl,
                'agora_name' => self::escape($agora->getName()),
                'agora_president' => self::escape($agora->president?->getFullName() ?? ''),
            ],
            ['first_name' => self::escape($first->getFirstName())]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName(), ['first_name' => self::escape($adherent->getFirstName())]);
        }

        return $message;
    }
}
