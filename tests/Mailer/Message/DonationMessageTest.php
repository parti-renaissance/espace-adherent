<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Donation;
use AppBundle\Mailer\Message\DonationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class DonationMessageTest extends TestCase
{
    public function testCreateFromAdherent()
    {
        $donationUuid = Uuid::uuid4();

        $donation = $this->createMock(Donation::class);
        $donation->expects($this->once())->method('getUuid')->willReturn($donationUuid);
        $donation->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $donation->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $donation->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $message = DonationMessage::createFromDonation($donation);

        $this->assertInstanceOf(DonationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame($donationUuid, $message->getUuid());
        $this->assertEmpty($message->getVars());
        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'target_firstname' => 'Jérôme',
            ],
            $recipient->getVars()
        );
    }
}
