<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Donation;
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
            [
                'first_name' => 'Jean',
                'year' => 2019,
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'donator@example.com',
            'Jean Doe',
            [
                'first_name' => 'Jean',
                'year' => 2019,
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->donation = $this->createMock(Donation::class);

        $this->donation
            ->expects(self::once())
            ->method('getUuid')
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
        $this->donation
            ->expects(self::once())
            ->method('getDonatedAt')
            ->willReturn(new \DateTime('2018-02-25'))
        ;
    }

    protected function tearDown()
    {
        $this->donation = null;
    }
}
