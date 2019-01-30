<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class IdeaFinalizeMessage extends Message
{
    public static function createNotification(Adherent $adherent, string $ideaLink): self
    {
        return static::createMessage($adherent, $ideaLink, '645028', 'Votre proposition va être soumise aux votes !');
    }

    public static function createPreNotification(Adherent $adherent, string $ideaLink): self
    {
        return static::createMessage($adherent, $ideaLink, '648151', 'Plus que 3 jours pour finaliser votre proposition !');
    }

    private static function createMessage(Adherent $adherent, string $ideaLink, string $templateId, string $subject): self
    {
        $message = new self(
            Uuid::uuid4(),
            $templateId,
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            $subject,
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
