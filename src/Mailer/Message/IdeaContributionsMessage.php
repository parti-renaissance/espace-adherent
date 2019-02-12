<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class IdeaContributionsMessage extends Message
{
    public static function createWithContributions(
        Adherent $adherent,
        string $ideaName,
        string $ideaLink,
        int $contributorsCount,
        int $commentsCount
    ): self {
        return static::createMessage($adherent, $ideaName, $ideaLink, '693036', "Il est temps d'enrichir votre proposition !", $contributorsCount, $commentsCount);
    }

    public static function createWithoutContributions(Adherent $adherent, string $ideaName, string $ideaLink): self
    {
        return static::createMessage($adherent, $ideaName, $ideaLink, '693728', 'Il est temps de partager votre proposition !');
    }

    private static function createMessage(
        Adherent $adherent,
        string $ideaName,
        string $ideaLink,
        string $templateId,
        string $subject,
        int $contributorsCount = 0,
        int $commentsCount = 0
    ): self {
        $message = new self(
            Uuid::uuid4(),
            $templateId,
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            $subject,
            [
                'first_name' => $adherent->getFirstName(),
                'idea_link' => $ideaLink,
                'idea_name' => $ideaName,
                'count_contributors' => $contributorsCount,
                'count_comments' => $commentsCount,
            ]
        );

        $message->setSenderEmail('atelier-des-idees@en-marche.fr');
        $message->setSenderName('La République En Marche !');

        return $message;
    }
}
