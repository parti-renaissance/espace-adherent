<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Invite;

final class InvitationMessage extends MailjetMessage
{
    public static function createFromInvite(Invite $invite): self
    {
        $message = new static(
            '61613',
            $invite->getEmail(),
            null,
            $invite->getFirstName().' '.$invite->getLastName().' vous invite à rejoindre En Marche'
        );

        $message->setVar('sender_firstname', $invite->getFirstName());
        $message->setVar('sender_lastname', $invite->getLastName());
        $message->setVar('target_message', $invite->getMessage());

        return $message;
    }
}
