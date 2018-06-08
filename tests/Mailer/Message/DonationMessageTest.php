<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Donation;
use AppBundle\Entity\Transaction;
use AppBundle\Mailer\Message\DonationMessage;
use Ramsey\Uuid\Uuid;

/**
 * @group message
 */
class DonationMessageTest extends MessageTestCase
{
    /**
     * @var Donation|null
     */
    private $donation;

    public function testCreate(): void
    {
        $message = DonationMessage::create($this->donation);

        self::assertMessage(
            DonationMessage::class,
            [],
            $message
        );

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'donator@example.com',
            'Jean Doe',
            ['recipient_first_name' => 'Jean'],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->donation = $this->createMock(Transaction::class);

        $this->donation
            ->expects(self::once())
            ->method('getDonationUuid')
            ->willReturn(Uuid::uuid4())
        ;
        $this->donation
            ->expects(self::once())
            ->method('getEmailAddress')
            ->willReturn('donator@example.com')
        ;
        $this->donation
            ->expects(self::once())
            ->method('getFullName')
            ->willReturn('Jean Doe')
        ;
        $this->donation
            ->expects(self::once())
            ->method('getFirstName')
            ->willReturn('Jean')
        ;
    }

    protected function tearDown()
    {
        $this->donation = null;
    }
}
