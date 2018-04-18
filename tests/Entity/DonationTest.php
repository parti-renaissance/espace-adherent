<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;
use AppBundle\Entity\PostAddress;
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

    private function createDonation(string $donatedAt): Donation
    {
        $donation = new Donation(
            Uuid::uuid4(),
            '10',
            'male',
            'jean',
            'dupont',
            'jp@j.p',
            $this->createMock(PostAddress::class),
            null,
            '127.0.0.1',
            PayboxPaymentSubscription::UNLIMITED
        );

        $reflectObject = new \ReflectionObject($donation);
        $reflectProp = $reflectObject->getProperty('donatedAt');
        $reflectProp->setAccessible(true);
        $reflectProp->setValue($donation, \DateTime::createFromFormat('Y/m/d H:i:s', $donatedAt));
        $reflectProp->setAccessible(false);

        return $donation;
    }
}
