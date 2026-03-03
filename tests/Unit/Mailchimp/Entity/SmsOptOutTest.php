<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Entity;

use App\Entity\SmsOptOut;
use App\Mailchimp\Contact\SmsOptOutSourceEnum;
use PHPUnit\Framework\TestCase;

final class SmsOptOutTest extends TestCase
{
    public function testConstructSetsPhoneSourceAndCreatedAt(): void
    {
        $phone = '+33612345678';

        $smsOptOut = new SmsOptOut($phone, SmsOptOutSourceEnum::Mailchimp);

        self::assertSame($phone, $smsOptOut->getPhone());
        self::assertSame(SmsOptOutSourceEnum::Mailchimp, $smsOptOut->getSource());
        self::assertInstanceOf(\DateTimeImmutable::class, $smsOptOut->getCreatedAt());
        self::assertFalse($smsOptOut->isCancelled());
        self::assertNull($smsOptOut->getCancelledAt());
    }

    public function testCancelSetsCancelledAt(): void
    {
        $smsOptOut = new SmsOptOut('+33612345678', SmsOptOutSourceEnum::Mailchimp);

        self::assertFalse($smsOptOut->isCancelled());

        $smsOptOut->cancel();

        self::assertTrue($smsOptOut->isCancelled());
        self::assertInstanceOf(\DateTimeImmutable::class, $smsOptOut->getCancelledAt());
    }
}
