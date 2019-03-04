<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class IdeaExtendMessage extends Message
{
    public static function create(Adherent $adherent, string $ideaLink): self
    {
        $message = new self(
            Uuid::uuid4(),
            '716215',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre proposition a 10 jours supplémentaires pour des contributions !',
            [
                'first_name' => $adherent->getFirstName(),
                'idea_link' => $ideaLink,
            ]
        );

        $message->setSenderEmail('atelier-des-idees@en-marche.fr');
        $message->setSenderName('La République En Marche !');

        return $message;
    }
}
