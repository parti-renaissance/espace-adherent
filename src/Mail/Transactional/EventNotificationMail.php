<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\DateTimeFormatter;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class EventNotificationMail extends TransactionalMail
{
    use AdherentMailTrait;

    private const SUBJECT_PATTERN = '%s - %s : Nouvel événement de %s : %s';

    public static function createRecipients(AdherentCollection $adherents): array
    {
        return $adherents
            ->map(function (Adherent $adherent) {
                return self::createRecipientFromAdherent($adherent, [
                    'target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName()),
                ]);
            })
            ->toArray()
        ;
    }

    public static function createTemplateVars(
        Event $event,
        Adherent $host,
        string $eventShowLink,
        string $eventAttendLink
    ): array {
        return [
            'animator_firstname' => StringCleaner::htmlspecialchars($host->getFirstName()),
            'event_name' => StringCleaner::htmlspecialchars($event->getName()),
            'event_date' => DateTimeFormatter::formatDate($event->getBeginAt(), 'EEEE d MMMM y'),
            'event_hour' => sprintf(
                '%sh%s',
                DateTimeFormatter::formatDate($event->getBeginAt(), 'HH'),
                DateTimeFormatter::formatDate($event->getBeginAt(), 'mm')
            ),
            'event_address' => StringCleaner::htmlspecialchars($event->getInlineFormattedAddress()),
            'event_slug' => $eventShowLink,
            'event-slug' => $eventShowLink,
            'event_ok_link' => $eventAttendLink,
            'event_ko_link' => $eventShowLink,
        ];
    }

    public static function createSubject(Event $event): string
    {
        return sprintf(
            self::SUBJECT_PATTERN,
            DateTimeFormatter::formatDate($event->getBeginAt(), 'd MMMM'),
            sprintf(
                '%sh%s',
                DateTimeFormatter::formatDate($event->getBeginAt(), 'HH'),
                DateTimeFormatter::formatDate($event->getBeginAt(), 'mm')
            ),
            $event->getCommittee()->getName(),
            StringCleaner::htmlspecialchars($event->getName())
        );
    }
}
