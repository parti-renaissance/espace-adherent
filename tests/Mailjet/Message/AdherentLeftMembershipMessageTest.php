<?php

namespace Tests\AppBundle\Mailjet\Message;

use AppBundle\Mailjet\Message\AdherentLeftMembershipMessage;
use AppBundle\Mailjet\Message\MailjetMessageRecipient;
use PHPUnit\Framework\TestCase;
use Tests\AppBundle\TestHelperTrait;

class AdherentLeftMembershipMessageTest extends TestCase
{
    use TestHelperTrait;

    public function testCreateAdherentLeftMembershipMessage()
    {
        $adherent = $this->getAdherentRepository()->findByEmail('michel.vasseur@example.ch');

        $message = AdherentLeftMembershipMessage::createFromAdherent($adherent);

        $this->assertInstanceOf(AdherentLeftMembershipMessage::class, $message);
        $this->assertSame('54665', $message->getTemplate()); // ROL TODO
        $this->assertSame('Votre départ d\'En Marche !', $message->getSubject());
        $this->assertCount(1, $message->getVars());
        $this->assertSame(['target_firstname' => ''], $message->getVars());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MailjetMessageRecipient::class, $recipient);
        $this->assertSame('michel.vasseur@example.ch', $recipient->getEmailAddress());
        $this->assertSame('Michel VASSEUR', $recipient->getFullName());
        $this->assertSame(
            [
                'target_firstname' => 'Michel',
                'confirmation_link' => 'https://localhost/activation',
            ],
            $recipient->getVars()
        );
    }
}
