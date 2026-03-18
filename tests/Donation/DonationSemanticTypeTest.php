<?php

declare(strict_types=1);

namespace Tests\App\Donation;

use App\Donation\DonationSemanticType;
use App\Entity\Donation;
use PHPUnit\Framework\TestCase;

class DonationSemanticTypeTest extends TestCase
{
    public function testFromDonationReturnsMembershipWhenIsMembership(): void
    {
        $donation = $this->createMock(Donation::class);
        $donation->method('isMembership')->willReturn(true);
        $donation->method('isSubscription')->willReturn(false);

        $result = DonationSemanticType::fromDonation($donation);

        $this->assertSame(DonationSemanticType::MEMBERSHIP, $result);
        $this->assertSame('membership', $result->value);
    }

    public function testFromDonationReturnsRecurringWhenIsSubscription(): void
    {
        $donation = $this->createMock(Donation::class);
        $donation->method('isMembership')->willReturn(false);
        $donation->method('isSubscription')->willReturn(true);

        $result = DonationSemanticType::fromDonation($donation);

        $this->assertSame(DonationSemanticType::RECURRING, $result);
        $this->assertSame('recurring', $result->value);
    }

    public function testFromDonationReturnsSimpleWhenNotMembershipNorSubscription(): void
    {
        $donation = $this->createMock(Donation::class);
        $donation->method('isMembership')->willReturn(false);
        $donation->method('isSubscription')->willReturn(false);

        $result = DonationSemanticType::fromDonation($donation);

        $this->assertSame(DonationSemanticType::SIMPLE, $result);
        $this->assertSame('simple', $result->value);
    }
}
