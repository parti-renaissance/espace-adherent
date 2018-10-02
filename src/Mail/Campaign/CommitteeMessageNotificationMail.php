<?php

namespace AppBundle\Mail\Campaign;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;

final class CommitteeMessageNotificationMail extends CampaignMail
{
    use AdherentMailTrait;

    public static function createRecipientsFrom(array $adherents): array
    {
        return array_map(function (Adherent $adherent) {
            return self::createRecipientFromAdherent(
                $adherent,
                ['target_firstname' => StringCleaner::htmlspecialchars($adherent->getFirstName())]
            );
        }, $adherents);
    }

    public static function createTemplateVars(CommitteeFeedItem $item): array
    {
        return [
            'animator_firstname' => StringCleaner::htmlspecialchars($item->getAuthorFirstName()),
            'target_message' => $item->getContent(),
        ];
    }

    public static function createSubject(string $subject): string
    {
        return "[Comité local] ${subject}";
    }

    public static function createSender(Adherent $author): SenderInterface
    {
        return new Sender(null, $author->getFullName());
    }
}
