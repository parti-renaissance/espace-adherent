<?php

namespace Tests\App\Mailer\Message;

use App\Entity\Adherent;
use App\Mailer\Message\AdherentAccountConfirmationMessage;
use App\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class AdherentAccountConfirmationMessageTest extends TestCase
{
    public function testCreateAdherentAccountConfirmationMessage()
    {
        $adherent = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $adherent->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $message = AdherentAccountConfirmationMessage::createFromAdherent($adherent);

        $this->assertSame('adherent-account-confirmation', $message->generateTemplateName());
        $this->assertSame('Et maintenant ?', $message->getSubject());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(['target_firstname' => 'Jérôme'], $recipient->getVars());
    }
}
