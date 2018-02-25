<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventRegistration;
use Ramsey\Uuid\Uuid;

final class CitizenActionContactParticipantsMessage extends Message
{
    public static function create(array $recipients, Adherent $organizer, string $subject, string $message): self
    {
        if (!$recipients) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $recipient = array_shift($recipients);
        if (!$recipient instanceof EventRegistration) {
            throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', EventRegistration::class));
        }

        $message = new self(
            Uuid::uuid4(),
            $recipient->getEmailAddress(),
            $recipient->getFullName(),
            static::getTemplateVars($organizer, $subject, $message),
            [],
            $organizer->getEmailAddress()
        );

        $message->setSenderName($organizer->getFullName());

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof EventRegistration) {
                throw new \InvalidArgumentException(sprintf('This message builder requires a collection of %s instances', EventRegistration::class));
            }

            $message->addRecipient(
                $recipient->getEmailAddress(),
                $recipient->getFullname()
            );
        }

        return $message;
    }

    private static function getTemplateVars(Adherent $organizer, string $subject, string $message): array
    {
        return [
            'citizen_project_host_first_name' => self::escape($organizer->getFirstName()),
            'citizen_project_host_subject' => $subject,
            'citizen_project_host_message' => $message,
        ];
    }
}
