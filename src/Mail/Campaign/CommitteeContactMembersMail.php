<?php

namespace AppBundle\Mail\Campaign;

use AppBundle\Entity\Adherent;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;

final class CommitteeContactMembersMail extends CampaignMail
{
    use AdherentMailTrait;

    public static function createRecipients(array $adherents): array
    {
        return array_map(
            function (Adherent $adherent) {
                return self::createRecipientFromAdherent(
                    $adherent,
                    ['target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
                );
            },
            $adherents
        );
    }

    public static function createTemplateVars(Adherent $author, string $content): array
    {
        return [
            'animator_firstname' => StringCleaner::htmlspecialchars($author->getFirstName()),
            'target_message' => $content,
        ];
    }

    public static function createSubject(string $title): string
    {
        return '[Comité local] '.$title;
    }

    public static function createSender(Adherent $author): SenderInterface
    {
        return new Sender(null, $author->getFullName());
    }
}
