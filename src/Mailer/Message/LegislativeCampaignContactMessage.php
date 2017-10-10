<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Legislative\LegislativeCampaignContactMessage as CampaignContactMessage;
use Ramsey\Uuid\Uuid;

final class LegislativeCampaignContactMessage extends Message
{
    public static function createFromCampaignContactMessage(CampaignContactMessage $contact, string $recipient): self
    {
        $message = new self(
            Uuid::uuid4(),
            $recipient,
            null,
            [
                'email' => static::escape($contact->getEmailAddress()),
                'first_name' => static::escape($contact->getFirstName()),
                'last_name' => static::escape($contact->getLastName()),
                'department_number' => static::escape($contact->getDepartmentNumber()),
                'electoral_district_number' => static::escape($contact->getElectoralDistrictNumber()),
                'role' => static::escape($contact->getRole()),
                'subject' => static::escape($contact->getSubject()),
                'message' => nl2br(static::escape($contact->getMessage())),
            ]
        );

        $message->setSenderName($contact->getFullName());
        $message->addCC($contact->getEmailAddress());

        return $message;
    }
}
