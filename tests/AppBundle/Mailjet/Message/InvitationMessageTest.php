<?php

namespace Tests\AppBundle\Mailjet\Message;

use AppBundle\Entity\Invite;
use AppBundle\Mailjet\Message\InvitationMessage;

class InvitationMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateInvitationMessageFromInvite()
    {
        $message = InvitationMessage::createFromInvite(Invite::create(
            'Paul',
            'Auffray',
            'jerome.picon@gmail.tld',
            'Vous êtes invités par Paul Auffray !',
            '192.168.12.25'
        ));

        $this->assertInstanceOf(InvitationMessage::class, $message);
        $this->assertSame('61613', $message->getTemplate());
        $this->assertSame(['jerome.picon@gmail.tld', null], $message->getRecipient());
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
    }
}
