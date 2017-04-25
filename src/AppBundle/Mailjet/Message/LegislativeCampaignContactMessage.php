<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Legislative\LegislativeCampaignContactMessage as CampaignContactMessage;
use Ramsey\Uuid\Uuid;

final class LegislativeCampaignContactMessage extends MailjetMessage
{
    public static function createFromCampaignContactMessage(CampaignContactMessage $contact, string $recipient): self
    {
        $message = new self(
            Uuid::uuid4(),
            '143247',
            $recipient,
            null,
            'Élections Législatives - Nouvelle demande de contact',
            [
                'expediteur' => '', // @todo to be removed?
                'email' => static::escape($contact->getEmailAddress()),
                'prenom' => static::escape($contact->getFirstName()),
                'nom' => static::escape($contact->getLastName()),
                'numero_de_departement' => static::escape($contact->getDepartmentNumber()),
                'numero_circonscription' => static::escape($contact->getElectoralDistrictNumber()),
                'role' => static::escape($contact->getRole()),
                'sujet' => static::escape($contact->getSubject()),
                'message' => nl2br(static::escape($contact->getMessage())),
            ],
            [],
            $contact->getEmailAddress()
        );
        $message->setSenderName($contact->getFullName());
        $message->setSenderEmail($contact->getEmailAddress());
        $message->addCC($contact->getEmailAddress());

        return $message;
    }
}
