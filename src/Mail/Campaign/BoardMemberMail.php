<?php

namespace AppBundle\Mail\Campaign;

use AppBundle\BoardMember\BoardMemberMessage;
use AppBundle\Entity\Adherent;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\Mail\Sender;
use EnMarche\MailerBundle\Mail\SenderInterface;

final class BoardMemberMail extends CampaignMail
{
    use AdherentMailTrait;

    private const SENDER_EMAIL = 'jemarche@en-marche.fr';

    public static function createTemplateVarsFrom(BoardMemberMessage $message): array
    {
        return [
            'member_firstname' => StringCleaner::htmlspecialchars($message->getFrom()->getFirstName()),
            'member_lastname' => StringCleaner::htmlspecialchars($message->getFrom()->getLastName()),
            'target_message' => $message->getContent(),
        ];
    }

    /**
     * @return RecipientInterface[]
     */
    public static function createRecipientsFrom(BoardMemberMessage $message): array
    {
        return array_merge(
            [new Recipient('jemarche@en-marche.fr', 'Je Marche')],
            array_map(
                function (Adherent $adherent) {
                    return self::createRecipientFromAdherent($adherent, []);
                },
                $message->getRecipients()
            )
        );
    }

    public static function createReplyToFrom(BoardMemberMessage $message): RecipientInterface
    {
        return new Recipient($message->getFrom()->getEmailAddress(), $message->getFrom()->getFullName());
    }

    public static function createSenderFrom(BoardMemberMessage $message): SenderInterface
    {
        return new Sender(self::SENDER_EMAIL, $message->getFrom()->getFullName());
    }
}
