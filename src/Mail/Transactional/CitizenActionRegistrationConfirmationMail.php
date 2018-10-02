<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class CitizenActionRegistrationConfirmationMail extends TransactionalMail
{
    public static function createRecipient(EventRegistration $registration): RecipientInterface
    {
        return new Recipient(
            $registration->getEmailAddress(),
            $registration->getFirstName(),
            ['prenom' => StringCleaner::htmlspecialchars($registration->getFirstName())]
        );
    }

    public static function createTemplateVars(CitizenAction $action, string $link): array
    {
        return [
            'citizen_action_name' => StringCleaner::htmlspecialchars($action->getName()),
            'citizen_action_organiser' => StringCleaner::htmlspecialchars($action->getOrganizerName()),
            'citizen_action_calendar_url' => $link,
        ];
    }
}
