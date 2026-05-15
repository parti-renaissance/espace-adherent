<?php

declare(strict_types=1);

namespace Tests\App\Unit\NationalEvent\Handler;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\Entity\NationalEvent\PaymentStatus;
use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\Event\SuccessPaymentEvent;
use App\NationalEvent\Handler\PaymentStatusUpdateCommandHandler;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PaymentStatusUpdateCommandHandlerTest extends TestCase
{
    public function testFailedRetryDoesNotDowngradeAlreadyConfirmedInscription(): void
    {
        $packageValues = ['transport' => 'dimanche_train', 'accommodation' => 'chambre_partagee', 'visitDay' => 'dimanche'];

        $inscription = $this->createInscription($packageValues, 9900);
        $inscription->status = InscriptionStatusEnum::PENDING;
        $inscription->paymentStatus = PaymentStatusEnum::CONFIRMED;

        // P1 already CONFIRMED.
        $firstPayment = new Payment(Uuid::v4(), $inscription, 9900, $packageValues, false);
        $firstPayment->setCreatedAt(new \DateTime('2026-01-01 10:00:00'));
        $firstPayment->setUpdatedAt(new \DateTime('2026-01-01 10:00:00'));
        $firstPayment->addStatus(new PaymentStatus($firstPayment, ['STATUS' => '9']));
        $inscription->addPayment($firstPayment);

        // P2 is a fresh retry, still PENDING when the cancellation webhook arrives.
        $secondPayment = new Payment($secondUuid = Uuid::v4(), $inscription, 9900, $packageValues, false);
        $secondPayment->setCreatedAt(new \DateTime('2026-01-02 10:00:00'));
        $secondPayment->setUpdatedAt(new \DateTime('2026-01-02 10:00:00'));
        $inscription->addPayment($secondPayment);

        $paymentRepository = $this->createMock(PaymentRepository::class);
        $paymentRepository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with($secondUuid->toRfc4122())
            ->willReturn($secondPayment)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $handler = new PaymentStatusUpdateCommandHandler(
            $entityManager,
            $eventDispatcher,
            $paymentRepository,
            $this->createStub(LoggerInterface::class),
        );

        // STATUS '4' → not 8/9 → ERROR
        $handler(new PaymentStatusUpdateCommand(['orderID' => $secondUuid->toRfc4122(), 'STATUS' => '4']));

        self::assertSame(PaymentStatusEnum::ERROR, $secondPayment->status, 'Failed payment itself must be marked as ERROR');
        self::assertSame(PaymentStatusEnum::CONFIRMED, $firstPayment->status, 'First confirmed payment must remain CONFIRMED');
        self::assertFalse($firstPayment->toRefund, 'First payment must not be flagged for refund by a failed retry');
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus, 'Inscription must remain CONFIRMED — failed retry must not downgrade it');
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
    }

    public function testSuccessfulPaymentSetsInscriptionConfirmed(): void
    {
        $packageValues = ['transport' => 'dimanche_train', 'accommodation' => 'chambre_partagee', 'visitDay' => 'dimanche'];

        $inscription = $this->createInscription($packageValues, 9900);
        $inscription->status = InscriptionStatusEnum::WAITING_PAYMENT;
        $inscription->paymentStatus = PaymentStatusEnum::PENDING;

        $payment = new Payment($paymentUuid = Uuid::v4(), $inscription, 9900, $packageValues, false);
        $payment->setCreatedAt(new \DateTime('2026-01-01 10:00:00'));
        $payment->setUpdatedAt(new \DateTime('2026-01-01 10:00:00'));
        $inscription->addPayment($payment);

        $paymentRepository = $this->createMock(PaymentRepository::class);
        $paymentRepository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with($paymentUuid->toRfc4122())
            ->willReturn($payment)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SuccessPaymentEvent::class))
        ;

        $handler = new PaymentStatusUpdateCommandHandler(
            $entityManager,
            $eventDispatcher,
            $paymentRepository,
            $this->createStub(LoggerInterface::class),
        );

        $handler(new PaymentStatusUpdateCommand(['orderID' => $paymentUuid->toRfc4122(), 'STATUS' => '9']));

        self::assertSame(PaymentStatusEnum::CONFIRMED, $payment->status);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
    }

    public function testFailedSolePaymentSetsInscriptionError(): void
    {
        $packageValues = ['transport' => 'dimanche_train', 'accommodation' => 'chambre_partagee', 'visitDay' => 'dimanche'];

        $inscription = $this->createInscription($packageValues, 9900);
        $inscription->status = InscriptionStatusEnum::WAITING_PAYMENT;
        $inscription->paymentStatus = PaymentStatusEnum::PENDING;

        $payment = new Payment($paymentUuid = Uuid::v4(), $inscription, 9900, $packageValues, false);
        $payment->setCreatedAt(new \DateTime('2026-01-01 10:00:00'));
        $payment->setUpdatedAt(new \DateTime('2026-01-01 10:00:00'));
        $inscription->addPayment($payment);

        $paymentRepository = $this->createMock(PaymentRepository::class);
        $paymentRepository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with($paymentUuid->toRfc4122())
            ->willReturn($payment)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $handler = new PaymentStatusUpdateCommandHandler(
            $entityManager,
            $eventDispatcher,
            $paymentRepository,
            $this->createStub(LoggerInterface::class),
        );

        $handler(new PaymentStatusUpdateCommand(['orderID' => $paymentUuid->toRfc4122(), 'STATUS' => '4']));

        self::assertSame(PaymentStatusEnum::ERROR, $payment->status);
        self::assertSame(PaymentStatusEnum::ERROR, $inscription->paymentStatus);
        self::assertSame(InscriptionStatusEnum::WAITING_PAYMENT, $inscription->status);
    }

    private function createInscription(array $packageValues, int $amount): EventInscription
    {
        $inscription = new EventInscription(new NationalEvent());
        $inscription->amount = $amount;
        $inscription->packageValues = $packageValues;
        $inscription->withDiscount = false;

        return $inscription;
    }
}
