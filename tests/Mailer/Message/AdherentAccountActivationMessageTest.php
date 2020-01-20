<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\AdherentAccountActivationMessage;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class AdherentAccountActivationMessageTest extends TestCase
{
    const CONFIRMATION_URL = 'https://test.enmarche.code/activation';

    public function testCreateAdherentAccountActivationMessage()
    {
        $adherent = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $adherent->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $message = AdherentAccountActivationMessage::create($adherent, self::CONFIRMATION_URL);

        $this->assertSame('adherent-account-activation', $message->generateTemplateName());
        $this->assertSame('Confirmez votre compte En-Marche.fr', $message->getSubject());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'first_name' => 'Jérôme',
                'activation_link' => self::CONFIRMATION_URL,
            ],
            $recipient->getVars()
        );
    }
}
