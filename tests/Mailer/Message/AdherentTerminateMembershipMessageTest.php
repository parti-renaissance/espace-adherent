<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\AdherentTerminateMembershipMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class AdherentTerminateMembershipMessageTest extends TestCase
{
    public function testCreateFromAdherent()
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('kevin@example.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Kévin CORNIL');
        $adherent->expects($this->once())->method('getFirstName')->willReturn('Kévin');

        $message = AdherentTerminateMembershipMessage::createFromAdherent($adherent);

        $this->assertInstanceOf(AdherentTerminateMembershipMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertEmpty($message->getVars());
        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('kevin@example.com', $recipient->getEmailAddress());
        $this->assertSame('Kévin CORNIL', $recipient->getFullName());
        $this->assertSame(
            [
                'target_firstname' => 'Kévin',
            ],
            $recipient->getVars()
        );
    }
}
