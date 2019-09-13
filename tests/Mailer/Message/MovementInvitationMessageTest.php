<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Invite;
use AppBundle\Mailer\Message\MessageRecipient;
use AppBundle\Mailer\Message\MovementInvitationMessage;
use PHPUnit\Framework\TestCase;

class MovementInvitationMessageTest extends TestCase
{
    public function testCreateInvitationMessageFromInvite()
    {
        $message = MovementInvitationMessage::createFromInvite(Invite::create(
            'Paul',
            'Auffray',
            'jerome.picon@gmail.tld',
            'Vous êtes invités par Paul Auffray !',
            '192.168.12.25'
        ));

        $this->assertSame('movement-invitation', $message->generateTemplateName());
        $this->assertSame('Paul Auffray vous invite à rejoindre En Marche.', $message->getSubject());
        $this->assertCount(3, $message->getVars());
        $this->assertSame(
            [
                'sender_firstname' => 'Paul',
                'sender_lastname' => 'Auffray',
                'target_message' => 'Vous êtes invités par Paul Auffray !',
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient('jerome.picon@gmail.tld');
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome.picon@gmail.tld', $recipient->getEmailAddress());
        $this->assertNull($recipient->getFullName());
    }
}
