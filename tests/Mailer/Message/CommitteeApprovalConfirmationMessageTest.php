<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\CommitteeApprovalConfirmationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class CommitteeApprovalConfirmationMessageTest extends TestCase
{
    public function testCreate()
    {
        $host = $this->createMock(Adherent::class);
        $host->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $host->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $host->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $message = CommitteeApprovalConfirmationMessage::create($host, 'Nice');

        $this->assertInstanceOf(CommitteeApprovalConfirmationMessage::class, $message);
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
                'animator_firstname' => 'Jérôme',
            ],
            $recipient->getVars()
        );
    }
}
