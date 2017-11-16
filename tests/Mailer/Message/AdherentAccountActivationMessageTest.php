<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\AdherentAccountActivationMessage;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class AdherentAccountActivationMessageTest extends TestCase
{
    const CONFIRMATION_URL = 'https://enmarche.dev/activation';

    public function testCreateAdherentAccountActivationMessage()
    {
        $adherent = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $adherent->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $message = AdherentAccountActivationMessage::createFromAdherent($adherent, self::CONFIRMATION_URL);

        $this->assertInstanceOf(AdherentAccountActivationMessage::class, $message);
        $this->assertSame('54665', $message->getTemplate());
        $this->assertSame('Plus qu\'une étape', $message->getSubject());
        $this->assertCount(2, $message->getVars());
        $this->assertSame(['target_firstname' => '', 'confirmation_link' => ''], $message->getVars());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'target_firstname' => 'Jérôme',
                'confirmation_link' => self::CONFIRMATION_URL,
            ],
            $recipient->getVars()
        );
    }
}
