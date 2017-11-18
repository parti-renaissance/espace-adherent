<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Mailer\Message\CommitteeContactMembersMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;

class CommitteeContactMembersMessageTest extends AbstractEventMessageTest
{
    public function testCreate()
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $host = $this->createAdherentMock('jerome@example.com', 'Jérôme', 'Pichou');
        $host->expects($this->once())->method('getGender')->willReturn('male');

        $message = CommitteeContactMembersMessage::create(
            $recipients,
            $host,
            'I like trains.'
        );

        $this->assertInstanceOf(CommitteeContactMembersMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('Jérôme, animateur de votre comité', $message->getSenderName());
        $this->assertSame('jerome@example.com', $message->getReplyTo());
        $this->assertCount(1, $message->getVars());
        $this->assertSame(
            [
                'target_message' => 'I like trains.',
            ],
            $message->getVars()
        );
        $this->assertCount(4, $message->getRecipients());

        $recipient = $message->getRecipient(0);

        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel Macron', $recipient->getFullName());
        $this->assertSame(
            [
                'target_message' => 'I like trains.',
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(3);

        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric Zitrone', $recipient->getFullName());
        $this->assertSame(
            [
                'target_message' => 'I like trains.',
            ],
            $recipient->getVars()
        );
    }
}
