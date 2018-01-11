<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class CitizenProjectApprovedSummaryMessage extends Message
{
    public static function create(
        Adherent $adherent,
        string $summary,
        string $allCitizenProjectsUrl,
        string $emailNotificationsUrl
    ): self {
        $message = new self(
            Uuid::uuid4(),
            '296766',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            '[Projets citoyens] Les projets citoyens près de chez vous !',
            [
                'target_firstname' => self::escape($adherent->getFirstName()),
                'citizen_project_list' => $summary,
                'all_citizen_projects_url' => $allCitizenProjectsUrl,
                'email_notifications_url' => $emailNotificationsUrl,
            ]
        );

        $message->setSenderEmail('projetscitoyens@en-marche.fr');

        return $message;
    }
}
