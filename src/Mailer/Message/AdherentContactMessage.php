<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Contact\ContactMessage;
use AppBundle\Mail\AdherentMailTrait;
use EnMarche\MailerBundle\Mail\CampaignMail;
use EnMarche\MailerBundle\Mail\RecipientInterface;

final class AdherentContactMessage extends CampaignMail
{
    use AdherentMailTrait;

    public static function createRecipientFor(ContactMessage $contactMessage): RecipientInterface
    {
        $recipient = $contactMessage->getTo();

        return self::createRecipientFromAdherent($recipient, [
            'recipient_first_name' => $recipient->getFirstName(),
            'sender_first_name' => $contactMessage->getFrom()->getFirstName(),
            'message' => \nl2br($contactMessage->getContent()),
        ]);
    }
}
