<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mailer\Message\CommitteeMessageNotificationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;

class CommitteeMessageNotificationMessageTest extends AbstractEventMessageTest
{
    public function testCreate()
    {
        $recipients[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $recipients[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $recipients[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $recipients[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $author = $this->createAdherentMock('jerome@example.com', 'Jérôme', 'Pichoud');
        $author->expects($this->once())->method('getGender')->willReturn('male');

        $feedItem = $this->createMock(CommitteeFeedItem::class);
        $feedItem->expects($this->once())->method('getAuthor')->willReturn($author);
        $feedItem->expects($this->once())->method('getContent')->willReturn('I like trains.');

        $message = CommitteeMessageNotificationMessage::create($recipients, $feedItem);

        $this->assertInstanceOf(CommitteeMessageNotificationMessage::class, $message);
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
