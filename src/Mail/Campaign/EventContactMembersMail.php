<?php

namespace AppBundle\Mail\Campaign;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;

final class EventContactMembersMail extends CampaignMail
{
    private const SUBJECT = '[Événement] %s';

    /**
     * @param EventRegistration[] $recipients
     */
    public static function createRecipientsFrom(array $registrations): array
    {
        return array_map(
            function (EventRegistration $registration) {
                return new Recipient(
                    $registration->getEmailAddress(),
                    $registration->getFirstName(),
                    ['target_firstname' => StringCleaner::htmlspecialchars($registration->getFirstName())]
                );
            },
            $registrations
        );
    }

    public static function createTemplateVars(Adherent $organizer, string $content): array
    {
        return [
            'organizer_firstname' => StringCleaner::htmlspecialchars($organizer->getFirstName()),
            'target_message' => $content,
        ];
    }

    public static function createSubject(string $subject): string
    {
        return sprintf(self::SUBJECT, $subject);
    }

    public static function createReplyToFrom(Adherent $organizer): RecipientInterface
    {
        return new Recipient($organizer->getEmailAddress());
    }
}
