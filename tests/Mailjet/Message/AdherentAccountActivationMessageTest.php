<?php

namespace Tests\AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailjet\Message\AdherentAccountActivationMessage;
use AppBundle\Mailjet\Message\MailjetMessageRecipient;

class AdherentAccountActivationMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAdherentAccountActivationMessage()
    {
        $adherent = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $adherent->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $message = AdherentAccountActivationMessage::createFromAdherent($adherent, 'https://localhost/activation');

        $this->assertInstanceOf(AdherentAccountActivationMessage::class, $message);
        $this->assertSame('54665', $message->getTemplate());
        $this->assertSame('Plus qu\'une étape', $message->getSubject());
        $this->assertCount(2, $message->getVars());
        $this->assertSame(['target_firstname' => '', 'confirmation_link' => ''], $message->getVars());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MailjetMessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'target_firstname' => 'Jérôme',
                'confirmation_link' => 'https://localhost/activation',
            ],
            $recipient->getVars()
        );
    }
}
