<?php

namespace AppBundle\Mail\Campaign;

use AppBundle\Collection\EventRegistrationCollection;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\Recipient;

final class CitizenActionCancellationMail extends CampaignMail
{
    use AdherentMailTrait;

    public const SUBJECT = '[Action citoyenne] Une action citoyenne à laquelle vous participez vient d\'être annulée.';

    public static function createRecipientsFrom(EventRegistrationCollection $subscriptions): array
    {
        return $subscriptions
            ->map(function (EventRegistration $registration) {
                return new Recipient(
                    $registration->getEmailAddress(),
                    $registration->getFullName(),
                    ['target_firstname' => StringCleaner::htmlspecialchars($registration->getFirstName())]
                );
            })
            ->toArray();
    }

    public static function createTemplateVarsFrom(CitizenAction $action, string $eventLink): array
    {
        return [
            'citizen_action_name' => StringCleaner::htmlspecialchars($action->getName()),
            'event_slug' => $eventLink,
        ];
    }
}
