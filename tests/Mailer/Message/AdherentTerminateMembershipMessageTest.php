<?php

namespace Tests\App\Mailer\Message;

use App\Entity\Adherent;
use App\Mailer\Message\AdherentTerminateMembershipMessage;
use App\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;
use Tests\App\TestHelperTrait;

class AdherentTerminateMembershipMessageTest extends TestCase
{
    use TestHelperTrait;

    public function testCreateAdherentTerminateMembershipMessage()
    {
        $adherent = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('kevin@example.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Kévin CORNIL');
        $adherent->expects($this->once())->method('getFirstName')->willReturn('Kévin');

        $message = AdherentTerminateMembershipMessage::createFromAdherent($adherent);

        $this->assertSame('adherent-terminate-membership', $message->generateTemplateName());
        $this->assertSame('Votre départ d\'En Marche !', $message->getSubject());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('kevin@example.com', $recipient->getEmailAddress());
        $this->assertSame('Kévin CORNIL', $recipient->getFullName());
        $this->assertSame(['target_firstname' => 'Kévin'], $recipient->getVars());
    }

    protected function tearDown()
    {
        $this->cleanupContainer($this->container);

        $this->container = null;
        $this->adherents = null;

        parent::tearDown();
    }
}
