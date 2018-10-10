<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Legislative\LegislativeCampaignContactMessage as CampaignContactMessage;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class LegislativeCampaignContactMail extends TransactionalMail
{
    public const SUBJECT = 'Élections Législatives - Nouvelle demande de contact';

    public static function createRecipient(string $email): RecipientInterface
    {
        return new Recipient($email);
    }

    public static function createTemplateVars(CampaignContactMessage $contact): array
    {
        return [
            'email' => StringCleaner::htmlspecialchars($contact->getEmailAddress()),
            'first_name' => StringCleaner::htmlspecialchars($contact->getFirstName()),
            'last_name' => StringCleaner::htmlspecialchars($contact->getLastName()),
            'department_number' => StringCleaner::htmlspecialchars($contact->getDepartmentNumber()),
            'electoral_district_number' => StringCleaner::htmlspecialchars($contact->getElectoralDistrictNumber()),
            'role' => StringCleaner::htmlspecialchars($contact->getRole()),
            'subject' => StringCleaner::htmlspecialchars($contact->getSubject()),
            'message' => nl2br(StringCleaner::htmlspecialchars($contact->getMessage())),
        ];
    }

    public static function createSender(CampaignContactMessage $contact): SenderInterface
    {
        return new Sender(null, $contact->getFullName());
    }

    public static function createCcRecipients(CampaignContactMessage $contact): array
    {
        return [new Recipient($contact->getEmailAddress())];
    }
}
