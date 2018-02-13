<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Contact\ContactMessage;
use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\AdherentContactMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class AdherentContactMessageTest extends TestCase
{
    public function testCreateFromModel()
    {
        $from = $this->createMock(Adherent::class);
        $from->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $from->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $to = $this->createMock(Adherent::class);
        $to->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $to->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');

        $contactMessage = $this->createMock(ContactMessage::class);
        $contactMessage->expects($this->once())->method('getContent')->willReturn('I like trains.');
        $contactMessage->expects($this->any())->method('getFrom')->willReturn($from);
        $contactMessage->expects($this->any())->method('getTo')->willReturn($to);

        $message = AdherentContactMessage::createFromModel($contactMessage);

        $this->assertInstanceOf(AdherentContactMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertEmpty($message->getVars());
        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'from_firstname' => 'Jérôme',
                'target_message' => 'I like trains.',
            ],
            $recipient->getVars()
        );
    }
}
