<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class IdeaPublishMessage extends Message
{
    public static function create(Adherent $adherent, string $ideaLink): self
    {
        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Votre proposition a bien été publiée !',
            [
                'first_name' => $adherent->getFirstName(),
                'idea_link' => $ideaLink,
            ]
        );

        $message->setSenderEmail('atelier-des-idees@en-marche.fr');

        return $message;
    }
}
