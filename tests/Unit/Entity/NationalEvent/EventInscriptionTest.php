<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\Entity\NationalEvent\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EventInscriptionTest extends TestCase
{
    public function testHasConfirmedPaymentForCurrentPackageReturnsFalseWithoutPayment(): void
    {
        $inscription = $this->createInscription(['transport' => 'train'], 9900);

        self::assertFalse($inscription->hasConfirmedPaymentForCurrentPackage());
    }

    public function testHasConfirmedPaymentForCurrentPackageReturnsTrueWhenConfirmedPaymentMatches(): void
    {
        $inscription = $this->createInscription($values = ['transport' => 'train'], 9900);

        $payment = $this->createConfirmedPayment($inscription, 9900, $values);
        $inscription->addPayment($payment);

        self::assertTrue($inscription->hasConfirmedPaymentForCurrentPackage());
    }

    public function testHasConfirmedPaymentForCurrentPackageReturnsFalseWhenAmountDiffers(): void
    {
        $inscription = $this->createInscription($values = ['transport' => 'train'], 9900);

        // Confirmed payment exists but at a different amount (e.g. before discount).
        $payment = $this->createConfirmedPayment($inscription, 12000, $values);
        $inscription->addPayment($payment);

        self::assertFalse($inscription->hasConfirmedPaymentForCurrentPackage());
    }

    public function testHasConfirmedPaymentForCurrentPackageReturnsFalseWhenPackageValuesDiffer(): void
    {
        $inscription = $this->createInscription(['transport' => 'train'], 9900);

        $payment = $this->createConfirmedPayment($inscription, 9900, ['transport' => 'bus']);
        $inscription->addPayment($payment);

        self::assertFalse($inscription->hasConfirmedPaymentForCurrentPackage());
    }

    public function testHasConfirmedPaymentForCurrentPackageIgnoresPaymentsFlaggedToRefund(): void
    {
        $inscription = $this->createInscription($values = ['transport' => 'train'], 9900);

        $payment = $this->createConfirmedPayment($inscription, 9900, $values);
        $payment->markAsToRefund();
        $inscription->addPayment($payment);

        self::assertFalse($inscription->hasConfirmedPaymentForCurrentPackage());
    }

    private function createInscription(array $packageValues, int $amount): EventInscription
    {
        $inscription = new EventInscription(new NationalEvent());
        $inscription->amount = $amount;
        $inscription->packageValues = $packageValues;
        $inscription->withDiscount = false;

        return $inscription;
    }

    private function createConfirmedPayment(EventInscription $inscription, int $amount, array $packageValues): Payment
    {
        $payment = new Payment(Uuid::uuid4(), $inscription, $amount, $packageValues, false);
        $payment->setCreatedAt(new \DateTime());
        $payment->setUpdatedAt(new \DateTime());
        $payment->addStatus(new PaymentStatus($payment, ['STATUS' => '9']));

        return $payment;
    }
}
