<?php

declare(strict_types=1);

namespace Tests\App\Command;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\Payment;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\Payment\Worldline\HostedCheckoutClientInterface;
use App\NationalEvent\PaymentStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use OnlinePayments\Sdk\Communication\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractCommandTestCase;
use Tests\App\Test\Payment\Worldline\FakeWorldlineConnection;

#[Group('functional')]
class NationalEventUpdatePaymentCommandTest extends AbstractCommandTestCase
{
    private const COMMAND = 'app:national-event:update-payment';

    private ?EntityManagerInterface $em = null;
    private ?FakeWorldlineConnection $connection = null;

    public function testItReconcilesAPendingPaymentFromWorldline(): void
    {
        $payment = $this->createStalePayment();

        $this->runCommand(self::COMMAND);

        $this->em->clear();
        $payment = $this->em->getRepository(Payment::class)->findOneByUuid($payment->getUuid()->toRfc4122());

        self::assertSame(PaymentStatusEnum::CONFIRMED, $payment->status);
        self::assertSame(FakeWorldlineConnection::PAYMENT_ID, $payment->worldlinePaymentId);
    }

    public function testItExpiresOnlyPaymentsOlderThanTheCheckoutSession(): void
    {
        // Younger than the session: Worldline can still capture it, so it must survive the run.
        $recent = $this->createStalePayment(ageMinutes: HostedCheckoutClientInterface::SESSION_TIMEOUT_MINUTES + 30);
        $this->connection->statusCode = 5;
        $this->connection->status = 'AUTHORIZATION_REQUESTED';

        $this->runCommand(self::COMMAND);

        $this->em->clear();
        $recent = $this->em->getRepository(Payment::class)->findOneByUuid($recent->getUuid()->toRfc4122());

        self::assertNotSame(PaymentStatusEnum::EXPIRED, $recent->status, 'A payment still capturable by Worldline must not be expired.');
    }

    private function createStalePayment(int $ageMinutes = 300): Payment
    {
        $inscription = $this->em->getRepository(EventInscription::class)->findOneBy(['status' => InscriptionStatusEnum::WAITING_PAYMENT]);

        self::assertNotNull($inscription, 'A fixture inscription waiting for payment is required.');

        $payment = new Payment(Uuid::v4(), $inscription, 9900, $inscription->packageValues ?? []);
        $payment->hostedCheckoutId = FakeWorldlineConnection::HOSTED_CHECKOUT_ID;
        $payment->payload = ['hostedCheckoutId' => FakeWorldlineConnection::HOSTED_CHECKOUT_ID];
        $inscription->addPayment($payment);

        $this->em->persist($payment);
        $this->em->flush();

        $this->connection->primeCheckout($payment->getUuid()->toRfc4122(), $payment->amount);

        // findToCheck() only picks payments older than 20 minutes.
        $this->em->getConnection()->update(
            'national_event_inscription_payment',
            ['created_at' => new \DateTime('-'.$ageMinutes.' minutes')->format('Y-m-d H:i:s')],
            ['id' => $payment->getId()]
        );
        $this->em->clear();

        return $payment;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = $this->get(EntityManagerInterface::class);
        $this->connection = $this->get(Connection::class);
        $this->connection->reset();
    }

    protected function tearDown(): void
    {
        $this->connection?->reset();

        parent::tearDown();

        $this->em = null;
        $this->connection = null;
    }
}
