<?php

namespace AppBundle\Mail\Campaign;

use AppBundle\CitizenAction\CitizenActionContactParticipantsCommand;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;

final class CitizenActionContactParticipantsMail extends CampaignMail
{
    use AdherentMailTrait;

    public static function createRecipientsFrom(array $subscriptions): array
    {
        return \array_map(function (EventRegistration $registration) {
            return new Recipient($registration->getEmailAddress(), $registration->getFullName());
        }, $subscriptions);
    }

    public static function createTemplateVarsFrom(CitizenActionContactParticipantsCommand $command): array
    {
        return [
            'citizen_project_host_message' => $command->getMessage(),
            'citizen_project_host_firstname' => StringCleaner::htmlspecialchars($command->getSender()->getFirstName()),
        ];
    }

    public static function createSenderFrom(Adherent $adherent): SenderInterface
    {
        return new Sender(null, $adherent->getFullName());
    }

    public static function createSubject(CitizenActionContactParticipantsCommand $command): string
    {
        return '[Action citoyenne] '.$command->getSubject();
    }
}
