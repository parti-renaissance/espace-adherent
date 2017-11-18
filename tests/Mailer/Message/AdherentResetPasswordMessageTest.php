<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\AdherentResetPasswordMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class AdherentResetPasswordMessageTest extends TestCase
{
    private const RESET_URL = 'https://enmarche.dev/espace-adherent/changer-mot-de-passe';

    public function testCreateFromAdherent()
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $adherent->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $message = AdherentResetPasswordMessage::createFromAdherent($adherent, self::RESET_URL);

        $this->assertInstanceOf(AdherentResetPasswordMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertEmpty($message->getVars());
        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'target_firstname' => 'Jérôme',
                'reset_link' => self::RESET_URL,
            ],
            $recipient->getVars()
        );
    }
}
