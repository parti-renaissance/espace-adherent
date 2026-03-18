<?php

declare(strict_types=1);

namespace Tests\App\Donation;

use App\Donation\DonationGlobalStatus;
use App\Entity\Donation;
use PHPUnit\Framework\TestCase;

class DonationGlobalStatusTest extends TestCase
{
    public function testFromDonationStatusReturnsPaidForFinished(): void
    {
        $result = DonationGlobalStatus::fromDonationStatus(Donation::STATUS_FINISHED);

        $this->assertSame(DonationGlobalStatus::PAID, $result);
        $this->assertSame('paid', $result->value);
    }

    public function testFromDonationStatusReturnsPaidForSubscriptionInProgress(): void
    {
        $result = DonationGlobalStatus::fromDonationStatus(Donation::STATUS_SUBSCRIPTION_IN_PROGRESS);

        $this->assertSame(DonationGlobalStatus::PAID, $result);
        $this->assertSame('paid', $result->value);
    }

    public function testFromDonationStatusReturnsRefundedForRefunded(): void
    {
        $result = DonationGlobalStatus::fromDonationStatus(Donation::STATUS_REFUNDED);

        $this->assertSame(DonationGlobalStatus::REFUNDED, $result);
        $this->assertSame('refunded', $result->value);
    }

    public function testFromDonationStatusReturnsFailedForWaitingConfirmation(): void
    {
        $result = DonationGlobalStatus::fromDonationStatus(Donation::STATUS_WAITING_CONFIRMATION);

        $this->assertSame(DonationGlobalStatus::FAILED, $result);
        $this->assertSame('failed', $result->value);
    }

    public function testFromDonationStatusReturnsFailedForError(): void
    {
        $result = DonationGlobalStatus::fromDonationStatus(Donation::STATUS_ERROR);

        $this->assertSame(DonationGlobalStatus::FAILED, $result);
        $this->assertSame('failed', $result->value);
    }

    public function testFromDonationStatusReturnsFailedForCanceled(): void
    {
        $result = DonationGlobalStatus::fromDonationStatus(Donation::STATUS_CANCELED);

        $this->assertSame(DonationGlobalStatus::FAILED, $result);
        $this->assertSame('failed', $result->value);
    }
}
