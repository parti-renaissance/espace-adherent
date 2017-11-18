<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\CommitteeCreationConfirmationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;

class CommitteeCreationConfirmationMessageTest extends AbstractEventMessageTest
{
    public function testCreate()
    {
        $adherent = $this->createAdherentMock('jerome@example.com', 'Jérôme', 'Pichoud');

        $message = CommitteeCreationConfirmationMessage::create($adherent, 'Nice');

        $this->assertInstanceOf(CommitteeCreationConfirmationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(1, $message->getVars());
        $this->assertSame(
            [
                'committee_city' => 'Nice',
            ],
            $message->getVars()
        );
        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'committee_city' => 'Nice',
                'target_firstname' => 'Jérôme',
            ],
            $recipient->getVars()
        );
    }
}
