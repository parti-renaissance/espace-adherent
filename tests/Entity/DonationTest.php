<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;
use AppBundle\Entity\PostAddress;
use AppBundle\Entity\Transaction;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class DonationTest extends TestCase
{
    public function donationProvider(): iterable
    {
        yield 'current month' => [
            '2018/05/03 11:51:21',
            '2018/05/03 11:51:21',
            '2018/06/03 11:51:21',
        ];
        yield 'previous month' => [
            '2018/04/03 11:51:21',
            '2018/05/03 11:51:21',
            '2018/06/03 11:51:21',
        ];
        yield 'first month' => [
            '2018/01/03 11:51:21',
            '2018/05/03 11:51:21',
            '2018/06/03 11:51:21',
        ];
        yield 'last month on previous year' => [
            '2017/12/03 11:51:21',
            '2018/05/03 11:51:21',
            '2018/06/03 11:51:21',
        ];
        yield 'from last month with next year on target' => [
            '2017/11/03 11:51:21',
            '2017/12/03 11:51:21',
            '2018/01/03 11:51:21',
        ];
    }

    /**
     * @dataProvider donationProvider
     */
    public function testNextDonationAt(string $donatedAt, string $fromDay, string $expected): void
    {
        $donation = $this->createDonation($donatedAt);

        static::assertSame(
            $expected,
            $donation->nextDonationAt(\DateTime::createFromFormat('Y/m/d H:i:s', $fromDay))->format('Y/m/d H:i:s')
        );
    }

    private function createDonation(
        string $donatedAt = null,
        string $type = Donation::TYPE_CB,
        string $duration = PayboxPaymentSubscription::UNLIMITED
    ): Donation {
        return new Donation(
            Uuid::uuid4(),
            $type,
            '10',
            $donatedAt ? \DateTimeImmutable::createFromFormat('Y/m/d H:i:s', $donatedAt) : new \DateTimeImmutable(),
            $this->createMock(PostAddress::class),
            '127.0.0.1',
            $duration,
            '10'
        );
    }

    public function processPayloadProvider(): iterable
    {
        $uuid = Uuid::uuid4();
        yield 'success_without_subscription' => [
            new Donation(
                $uuid,
                'cb',
                '10',
                \DateTimeImmutable::createFromFormat('Y/m/d H:i:s', '2018/02/02 15:16:17'),
                $this->createMock(PostAddress::class),
                '127.0.0.1',
                PayboxPaymentSubscription::NONE,
                '10'
            ),
            [
                'result' => Transaction::PAYBOX_SUCCESS,
                'transaction' => '42',
                'date' => '02022018',
                'time' => '15:16:17',
                'authorization' => 'XXXXXX',
                'subscription' => '0',
            ],
            [
                'isFinished' => true,
                'isSubscriptionInProgress' => false,
                'getStatus' => Donation::STATUS_FINISHED,
            ],
        ];
        yield 'success_subscription' => [
            new Donation(
                $uuid,
                'cb',
                '10',
                \DateTimeImmutable::createFromFormat('Y/m/d H:i:s', '2018/02/02 15:16:17'),
                $this->createMock(PostAddress::class),
                '127.0.0.1',
                PayboxPaymentSubscription::UNLIMITED,
                '10'
            ),
            [
                'result' => Transaction::PAYBOX_SUCCESS,
                'transaction' => '42',
                'date' => '02022018',
                'time' => '15:16:17',
                'authorization' => 'XXXXXX',
                'subscription' => '21',
            ],
            [
                'isFinished' => false,
                'isSubscriptionInProgress' => true,
                'getStatus' => Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
            ],
        ];
    }

    /**
     * @dataProvider processPayloadProvider
     */
    public function testProcessPayload(Donation $donation, array $payload, array $expectations): void
    {
        $donation->processPayload($payload);

        foreach ($expectations as $key => $expectation) {
            if ($expectation instanceof \DateTimeInterface) {
                self::assertSame($expectation->format('YmdHis'), $donation->$key()->format('YmdHis'));
            } else {
                self::assertSame($expectation, $donation->$key());
            }
        }
    }

    public function testStopSubscription(): void
    {
        $donation = $this->createDonation();

        self::assertTrue($donation->isWaitingConfirmation());
        self::assertFalse($donation->isSubscriptionInProgress());
        self::assertFalse($donation->isFinished());
        self::assertFalse($donation->isCanceled());

        $donation->processPayload([
            'result' => Transaction::PAYBOX_SUCCESS,
            'transaction' => '42',
            'date' => '02022018',
            'time' => '15:16:17',
            'authorization' => 'XXXXXX',
            'subscription' => '21',
        ]);

        self::assertFalse($donation->isWaitingConfirmation());
        self::assertTrue($donation->isSubscriptionInProgress());
        self::assertFalse($donation->isFinished());
        self::assertFalse($donation->isCanceled());

        $donation->stopSubscription();

        self::assertFalse($donation->isWaitingConfirmation());
        self::assertFalse($donation->isSubscriptionInProgress());
        self::assertFalse($donation->isFinished());
        self::assertTrue($donation->isCanceled());
    }

    public function testDatesForCB(): void
    {
        $donation = $this->createDonation('2020/01/12 12:30:00', Donation::TYPE_CB, PayboxPaymentSubscription::UNLIMITED);

        // No transaction yet
        self::assertSame('2020/01/12 12:30:00', $donation->getDonatedAt()->format('Y/m/d H:i:s'));
        self::assertNull($donation->getLastSuccessDate());

        $donation->processPayload([
            'result' => Transaction::PAYBOX_SUCCESS,
            'transaction' => '42',
            'date' => '12012020',
            'time' => '12:31:00',
            'authorization' => 'XXXXXX',
            'subscription' => '21',
        ]);

        // Last successful transaction should be used as last success date
        self::assertSame('2020/01/12 12:30:00', $donation->getDonatedAt()->format('Y/m/d H:i:s'));
        self::assertSame('2020/01/12 12:31:00', $donation->getLastSuccessDate()->format('Y/m/d H:i:s'));

        $donation->processPayload([
            'result' => Transaction::PAYBOX_SUCCESS,
            'transaction' => '42',
            'date' => '12012020',
            'time' => '11:31:00',
            'authorization' => 'XXXXXX',
            'subscription' => '21',
        ]);

        // Previous successful transaction should not be used as last success date
        self::assertSame('2020/01/12 12:30:00', $donation->getDonatedAt()->format('Y/m/d H:i:s'));
        self::assertSame('2020/01/12 12:31:00', $donation->getLastSuccessDate()->format('Y/m/d H:i:s'));

        $donation->processPayload([
            'result' => Transaction::PAYBOX_CARD_NUMBER_INVALID,
            'transaction' => '42',
            'date' => '14012020',
            'time' => '15:00:00',
            'authorization' => 'XXXXXX',
            'subscription' => '21',
        ]);

        // Transaction in error should not be used as last success date
        self::assertSame('2020/01/12 12:30:00', $donation->getDonatedAt()->format('Y/m/d H:i:s'));
        self::assertSame('2020/01/12 12:31:00', $donation->getLastSuccessDate()->format('Y/m/d H:i:s'));

        $donation->processPayload([
            'result' => Transaction::PAYBOX_SUCCESS,
            'transaction' => '42',
            'date' => '14012020',
            'time' => '15:00:00',
            'authorization' => 'XXXXXX',
            'subscription' => '21',
        ]);

        // New successful transaction should be used as last success date
        self::assertSame('2020/01/12 12:30:00', $donation->getDonatedAt()->format('Y/m/d H:i:s'));
        self::assertSame('2020/01/14 15:00:00', $donation->getLastSuccessDate()->format('Y/m/d H:i:s'));
    }

    public function testDatesForCheckAndTransfer(): void
    {
        $donation = $this->createDonation('2020/01/12 12:30:00', Donation::TYPE_CHECK, PayboxPaymentSubscription::NONE);

        self::assertSame('2020/01/12 12:30:00', $donation->getDonatedAt()->format('Y/m/d H:i:s'));
        self::assertSame('2020/01/12 12:30:00', $donation->getLastSuccessDate()->format('Y/m/d H:i:s'));

        $donation->setDonatedAt(\DateTimeImmutable::createFromFormat('Y/m/d H:i:s', '2020/01/02 09:30:00'));

        self::assertSame('2020/01/02 09:30:00', $donation->getDonatedAt()->format('Y/m/d H:i:s'));
        self::assertSame('2020/01/02 09:30:00', $donation->getLastSuccessDate()->format('Y/m/d H:i:s'));
    }
}
