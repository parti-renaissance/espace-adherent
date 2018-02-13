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
            self::getTemplateVars(
                $contact->getEmailAddress(),
                $contact->getFirstName(),
                $contact->getLastName(),
                $contact->getDepartmentNumber(),
                $contact->getElectoralDistrictNumber(),
                $contact->getRole(),
                $contact->getSubject(),
                $contact->getMessage()
            )
        );

        $message->setSenderName($contact->getFullName());
        $message->addCC($contact->getEmailAddress());

        return $message;
    }

    private static function getTemplateVars(
        ?string $emailAddress,
        ?string $firstName,
        ?string $lastName,
        ?string $departmentNumber,
        ?string $electoralDistrictNumber,
        ?string $role,
        ?string $subject,
        ?string $message
    ): array
    {
        return [
            'email' => static::escape($emailAddress),
            'first_name' => static::escape($firstName),
            'last_name' => static::escape($lastName),
            'department_number' => static::escape($departmentNumber),
            'electoral_district_number' => static::escape($electoralDistrictNumber),
            'role' => static::escape($role),
            'subject' => static::escape($subject),
            'message' => nl2br(static::escape($message)),
        ];
    }
}
