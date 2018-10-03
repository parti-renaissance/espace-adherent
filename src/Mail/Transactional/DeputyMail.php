<?php

namespace AppBundle\Mail\Transactional;

use AppBundle\Deputy\DeputyMessage;
use AppBundle\Entity\Adherent;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;
use EnMarche\MailerBundle\Mail\TransactionalMail;

final class DeputyMail extends TransactionalMail
{
    use AdherentMailTrait;

    public static function createRecipients(array $recipients): array
    {
        return array_map(function (Adherent $adherent) {
            return self::createRecipientFromAdherent(
                $adherent,
                ['target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
            );
        }, $recipients);
    }

    public static function createTemplateVars(DeputyMessage $message): array
    {
        $deputy = $message->getFrom();

        return [
            'deputy_fullname' => StringCleaner::htmlspecialchars($deputy->getFullName()),
            'circonscription_name' => StringCleaner::htmlspecialchars($deputy->getManagedDistrict()),
            'target_message' => $message->getContent(),
        ];
    }

    public static function createSender(Adherent $deputy): SenderInterface
    {
        return new Sender(null, sprintf('Votre député%s En Marche !', $deputy->isFemale() ? 'e' : ''));
    }
}
