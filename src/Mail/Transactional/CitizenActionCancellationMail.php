<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Collection\EventRegistrationCollection;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\ChunkableMailInterface;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class CitizenActionCancellationMail extends TransactionalMail implements ChunkableMailInterface
{
    use AdherentMailTrait;

    public const SUBJECT = '[Action citoyenne] Une action citoyenne à laquelle vous participez vient d\'être annulée.';

    public static function createRecipients(EventRegistrationCollection $subscriptions): array
    {
        return $subscriptions
            ->map(function (EventRegistration $registration) {
                return new Recipient(
                    $registration->getEmailAddress(),
                    $registration->getFullName(),
                    ['target_firstname' => StringCleaner::htmlspecialchars($registration->getFirstName())]
                );
            })
            ->toArray()
        ;
    }

    public static function createTemplateVars(CitizenAction $action, string $eventLink): array
    {
        return [
            'citizen_action_name' => StringCleaner::htmlspecialchars($action->getName()),
            'event_slug' => $eventLink,
        ];
    }
}
