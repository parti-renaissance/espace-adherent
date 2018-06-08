<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use Ramsey\Uuid\Uuid;

class CitizenActionNotificationMessage extends Message
{
    public static function create(array $recipients, Adherent $host, CitizenAction $citizenAction, string $citizenActionAttendUrl): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof Adherent) {
            throw new \InvalidArgumentException(sprintf(
                'This message builder requires a collection of %s instances',
                Adherent::class
            ));
        }

        $message = new static(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($host, $citizenAction, $citizenActionAttendUrl),
            static::getRecipientVars($recipient),
            $host->getEmailAddress()
        );

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Adherent) {
                throw new \InvalidArgumentException(sprintf(
                    'This message builder requires a collection of %s instances',
                    Adherent::class
                ));
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullName(),
                static::getRecipientVars($recipient)
            );
        }

        return $message;
    }

    private static function getTemplateVars(Adherent $host, CitizenAction $citizenAction, string $citizenActionAttendUrl): array
    {
        return [
            'host_first_name' => static::escape($host->getFirstName()),
            'citizen_project_name' => static::escape($citizenAction->getCitizenProject()->getName()),
            'citizen_action_name' => static::escape($citizenAction->getName()),
            'citizen_action_date' => static::formatDate($citizenAction->getBeginAt(), 'EEEE d MMMM y'),
            'citizen_action_hour' => sprintf(
                '%sh%s',
                static::formatDate($citizenAction->getBeginAt(), 'HH'),
                static::formatDate($citizenAction->getBeginAt(), 'mm')
            ),
            'citizen_action_address' => static::escape($citizenAction->getInlineFormattedAddress()),
            'citizen_action_attend_url' => $citizenActionAttendUrl,
        ];
    }

    private static function getRecipientVars(Adherent $recipient): array
    {
        return [
            'recipient_first_name' => static::escape($recipient->getFirstName()),
        ];
    }

    private static function formatDate(\DateTimeInterface $date, string $format): string
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN,
            $format
        );

        return $formatter->format($date);
    }
}
