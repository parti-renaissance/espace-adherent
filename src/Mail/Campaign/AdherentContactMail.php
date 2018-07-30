<?php

namespace AppBundle\Mail\Campaign;

use AppBundle\Contact\ContactMessage;
use AppBundle\Mail\AdherentMailTrait;
use AppBundle\Utils\StringCleaner;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\RecipientInterface;

final class AdherentContactMail extends CampaignMail
{
    use AdherentMailTrait;

    public static function createRecipientFor(ContactMessage $contactMessage): RecipientInterface
    {
        $recipient = $contactMessage->getTo();

        return self::createRecipientFromAdherent($recipient, [
            'member_firstname' => StringCleaner::htmlspecialchars($contactMessage->getFrom()->getFirstName()),
            'target_message' => \nl2br(StringCleaner::htmlspecialchars($contactMessage->getContent())),
        ]);
    }
}
