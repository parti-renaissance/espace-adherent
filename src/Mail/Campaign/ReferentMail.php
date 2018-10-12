<?php

namespace AppBundle\Mail\Campaign;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Referent\ReferentMessage;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;

final class ReferentMail extends CampaignMail
{
    use AdherentMailTrait;

    public static function createRecipients(array $users): array
    {
        return array_map(function (ReferentManagedUser $user) {
            return new Recipient(
                $user->getEmail(),
                (string) $user->getFullName(),
                ['target_firstname' => StringCleaner::htmlspecialchars((string) $user->getFirstName())]
            );
        }, $users);
    }

    public static function createTemplateVars(ReferentMessage $message): array
    {
        return [
            'referant_firstname' => StringCleaner::htmlspecialchars($message->getFrom()->getFirstName()),
            'target_message' => $message->getContent(),
        ];
    }

    public static function createSender(Adherent $referent): SenderInterface
    {
        return new Sender(null, $referent->getFullName().' [Référent]');
    }
}
