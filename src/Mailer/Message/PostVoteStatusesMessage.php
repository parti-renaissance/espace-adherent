<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class PostVoteStatusesMessage extends Message
{
    /** @param Adherent[] $adherents */
    public static function create(array $adherents, string $convocationUrl): self
    {
        $adherent = array_shift($adherents);

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Vous avez voté pour Renaissance !',
            [
                'now' => self::formatDate(new \DateTime(), 'EEEE d MMMM y'),
                'convocation_url' => $convocationUrl,
            ],
            [
                'first_name' => $adherent->getFirstName(),
                'last_name' => $adherent->getLastName(),
            ]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient(
                $adherent->getEmailAddress(),
                $adherent->getFullName(),
                [
                    'first_name' => $adherent->getFirstName(),
                    'last_name' => $adherent->getLastName(),
                ]
            );
        }

        return self::updateSenderInfo($message);
    }

    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('contact@parti-renaissance.fr');
        $message->setSenderName('Renaissance');

        return $message;
    }
}
