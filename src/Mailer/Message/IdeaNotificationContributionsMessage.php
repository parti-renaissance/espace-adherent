<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class IdeaNotificationContributionsMessage extends Message
{
    public static function create(
        Adherent $adherent,
        string $ideaName,
        string $ideaLink,
        int $contributorsCount,
        int $commentsCount
    ): self {
        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            "Il est temps d'enrichir votre proposition !",
            [
                'first_name' => $adherent->getFirstName(),
                'idea_link' => $ideaLink,
                'idea_name' => $ideaName,
                'count_contributors' => $contributorsCount,
                'count_comments' => $commentsCount,
            ]
        );

        $message->setSenderEmail('atelier-des-idees@en-marche.fr');

        return $message;
    }
}
