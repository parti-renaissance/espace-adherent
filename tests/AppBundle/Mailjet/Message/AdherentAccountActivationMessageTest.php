<?php

namespace Tests\AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailjet\Message\AdherentAccountActivationMessage;

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
        $this->assertSame(['jerome@example.com', 'Jérôme Pichoud'], $message->getRecipient());
        $this->assertSame('Finalisez votre inscription au mouvement En Marche !', $message->getSubject());
        $this->assertCount(2, $message->getVars());
        $this->assertSame(
            [
                'target_firstname' => 'Jérôme',
                'confirmation_link' => 'https://localhost/activation',
            ],
            $message->getVars()
        );
    }
}
