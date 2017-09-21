<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\AdherentAccountConfirmationMessage;
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
        $this->assertSame('54673', $message->getTemplate());
        $this->assertSame('Et maintenant ?', $message->getSubject());
        $this->assertCount(4, $message->getVars());
        $this->assertSame(
            [
                'adherents_count' => 8,
                'committees_count' => 15,
                'target_firstname' => '',
                'target_lastname' => '',
            ],
            $message->getVars()
        );

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
