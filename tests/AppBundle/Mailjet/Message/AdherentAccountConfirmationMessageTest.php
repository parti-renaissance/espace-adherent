<?php

namespace Tests\AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailjet\Message\AdherentAccountConfirmationMessage;
use AppBundle\Mailjet\Message\MailjetMessageRecipient;

class AdherentAccountConfirmationMessageTest extends \PHPUnit_Framework_TestCase
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
        $this->assertSame('Confirmation de votre inscription au mouvement En Marche !', $message->getSubject());
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
        $this->assertInstanceOf(MailjetMessageRecipient::class, $recipient);
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
