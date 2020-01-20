<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class IdeaNotificationWithoutContributionsMessage extends Message
{
    public static function create(Adherent $adherent, string $ideaName, string $ideaLink): self
    {
        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Il est temps de partager votre proposition !',
            [
                'first_name' => $adherent->getFirstName(),
                'idea_link' => $ideaLink,
                'idea_name' => $ideaName,
            ]
        );

        $message->setSenderEmail('atelier-des-idees@en-marche.fr');
        $message->setSenderName('La République En Marche !');

        return $message;
    }
}
