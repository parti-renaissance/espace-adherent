<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\AdherentAccountConfirmationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class AdherentAccountConfirmationMessageTest extends TestCase
{
    public function testCreateAdherentAccountConfirmationMessage()
    {
        $adherent = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $adherent->expects($this->once())->method('getFirstName')->willReturn('Jérôme');
        $adherent->expects($this->once())->method('getLastName')->willReturn('Pichoud');

        $message = AdherentAccountConfirmationMessage::createFromAdherent($adherent, 8, 15);

        $this->assertInstanceOf(AdherentAccountConfirmationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(2, $message->getVars());
        $this->assertSame(
            [
                'adherents_count' => 8,
                'committees_count' => 15,
            ],
            $message->getVars()
        );
        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'adherents_count' => 8,
                'committees_count' => 15,
                'target_firstname' => 'Jérôme',
                'target_lastname' => 'Pichoud',
            ],
            $recipient->getVars()
        );
    }
}
