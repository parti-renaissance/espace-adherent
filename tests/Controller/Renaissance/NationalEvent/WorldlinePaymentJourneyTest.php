<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\Payment;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use OnlinePayments\Sdk\Communication\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Payment\Worldline\FakeWorldlineConnection;

/**
 * End-to-end coverage of the Worldline payment journey: checkout creation, browser return and signed webhook.
 *
 * The SDK is only faked at its connection, so the request building, the JSON serialisation and the webhook signature
 * are all exercised for real.
 */
#[Group('functional')]
class WorldlinePaymentJourneyTest extends AbstractWebTestCase
{
    use ControllerTestTrait;

    private const WEBHOOK_ID = 'test-webhook-id';
    private const WEBHOOK_SECRET = 'test-webhook-secret';
    private const WEBHOOK_URL_KEY = 'test-webhook-url-key';

    private ?EntityManagerInterface $em = null;
    private ?EventInscriptionRepository $eventInscriptionRepository = null;
    private ?FakeWorldlineConnection $connection = null;

    public function testCheckoutIsCreatedWithTheOrderOfThePaymentAndRedirectsToWorldline(): void
    {
        $payment = $this->createPendingPayment();

        $this->client->request('GET', $this->paymentProcessUrl($payment));
        $this->client->submitForm('Continuer vers ma banque');

        $this->assertClientIsRedirectedTo(FakeWorldlineConnection::REDIRECT_URL, $this->client);

        $created = $this->firstCreateCheckoutRequest();

        // The request really went through the SDK: assert what Worldline would have received.
        self::assertSame($payment->getUuid()->toRfc4122(), $created['order']['references']['merchantReference']);
        self::assertSame($payment->amount, $created['order']['amountOfMoney']['amount']);
        self::assertSame('EUR', $created['order']['amountOfMoney']['currencyCode']);
        self::assertSame('SALE', $created['cardPaymentMethodSpecificInput']['authorizationMode']);
        self::assertStringContainsString('payment='.$payment->getUuid()->toRfc4122(), $created['hostedCheckoutSpecificInput']['returnUrl']);

        $this->em->clear();
        $payment = $this->getRepository(Payment::class)->findOneByUuid($payment->getUuid()->toRfc4122());
        self::assertSame(FakeWorldlineConnection::HOSTED_CHECKOUT_ID, $payment->hostedCheckoutId);
    }

    public function testASecondSubmitReusesTheCheckoutAlreadyOpened(): void
    {
        $payment = $this->createPendingPayment();

        $this->client->request('GET', $this->paymentProcessUrl($payment));
        $this->client->submitForm('Continuer vers ma banque');
        $this->assertClientIsRedirectedTo(FakeWorldlineConnection::REDIRECT_URL, $this->client);

        $this->client->request('GET', $this->paymentProcessUrl($payment));
        $this->client->submitForm('Continuer vers ma banque');
        $this->assertClientIsRedirectedTo(FakeWorldlineConnection::REDIRECT_URL, $this->client);

        self::assertCount(1, $this->createCheckoutRequests(), 'A second submit must not open a second checkout session.');
    }

    public function testReturnConfirmsTheInscriptionFromTheApiResponse(): void
    {
        $payment = $this->createPendingPayment();
        $inscriptionUuid = $payment->inscription->getUuid()->toRfc4122();

        $this->openCheckout($payment);

        $this->client->request('GET', $this->paymentStatusUrl($payment, $inscriptionUuid));

        self::assertResponseRedirects();

        $this->em->clear();
        $inscription = $this->eventInscriptionRepository->findOneBy(['uuid' => $inscriptionUuid]);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);
    }

    public function testReturnOfAnAuthorisedPaymentShowsPendingAndDoesNotFailTheInscription(): void
    {
        $payment = $this->createPendingPayment();
        $inscriptionUuid = $payment->inscription->getUuid()->toRfc4122();

        $this->openCheckout($payment);

        // 5 = authorised, capture still to come: the payment must not be turned into an error.
        $this->connection->statusCode = 5;
        $this->connection->status = 'AUTHORIZATION_REQUESTED';

        $this->client->request('GET', $this->paymentStatusUrl($payment, $inscriptionUuid));
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->em->clear();
        $inscription = $this->eventInscriptionRepository->findOneBy(['uuid' => $inscriptionUuid]);
        self::assertSame(PaymentStatusEnum::PENDING, $inscription->paymentStatus);
    }

    public function testReturnIgnoresAPaymentBelongingToAnotherInscription(): void
    {
        $payment = $this->createPendingPayment();
        $otherPayment = $this->createPendingPayment('worldline-e2e-other@example.org');

        $this->openCheckout($payment);

        // The payment uuid comes from the query string: it must not resolve across inscriptions.
        $this->client->request('GET', $this->paymentStatusUrl($otherPayment, $payment->inscription->getUuid()->toRfc4122()));
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->em->clear();
        $inscription = $this->eventInscriptionRepository->findOneBy(['uuid' => $otherPayment->inscription->getUuid()->toRfc4122()]);
        self::assertNotSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);
    }

    public function testSignedWebhookConfirmsThePayment(): void
    {
        $payment = $this->createPendingPayment();
        $this->openCheckout($payment);

        $this->postWebhook($this->webhookBody($payment));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->em->clear();
        $inscription = $this->eventInscriptionRepository->findOneBy(['uuid' => $payment->inscription->getUuid()->toRfc4122()]);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);
        self::assertSame(InscriptionStatusEnum::PENDING, $inscription->status);
    }

    public function testWebhookWithAnInvalidSignatureIsRejected(): void
    {
        $payment = $this->createPendingPayment();
        $this->openCheckout($payment);

        $body = $this->webhookBody($payment);
        $this->postWebhook($body, signature: 'forged-signature');

        // Answering 200 keeps a caller from probing the endpoint, but nothing must be applied.
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertPaymentUntouched($payment);
    }

    public function testWebhookWithAWrongUrlKeyIsRejected(): void
    {
        $payment = $this->createPendingPayment();
        $this->openCheckout($payment);

        $this->postWebhook($this->webhookBody($payment), urlKey: 'wrong-url-key');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertPaymentUntouched($payment);
    }

    public function testWebhookForAnotherMerchantIsRejected(): void
    {
        $payment = $this->createPendingPayment();
        $this->openCheckout($payment);

        $this->postWebhook($this->webhookBody($payment, merchantId: 'someone-else'));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertPaymentUntouched($payment);
    }

    public function testWebhookWithAMismatchingAmountDoesNotConfirmThePayment(): void
    {
        $payment = $this->createPendingPayment();
        $this->openCheckout($payment);

        // A payload correlated by merchant reference but carrying another amount must never confirm.
        $this->connection->amountOverride = 1;
        $this->postWebhook($this->webhookBody($payment));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertPaymentUntouched($payment);
    }

    public function testTheSameWebhookDeliveredTwiceIsAppliedOnce(): void
    {
        $payment = $this->createPendingPayment();
        $this->openCheckout($payment);

        $body = $this->webhookBody($payment);
        $this->postWebhook($body);
        $this->postWebhook($body);

        $this->em->clear();
        $payment = $this->getRepository(Payment::class)->findOneByUuid($payment->getUuid()->toRfc4122());
        self::assertCount(1, $payment->getStatuses());
    }

    public function testReturnThenWebhookAppliesTheStatusOnlyOnce(): void
    {
        $payment = $this->createPendingPayment();
        $inscriptionUuid = $payment->inscription->getUuid()->toRfc4122();

        $this->openCheckout($payment);

        $this->client->request('GET', $this->paymentStatusUrl($payment, $inscriptionUuid));
        $this->postWebhook($this->webhookBody($payment));

        $this->em->clear();
        $payment = $this->getRepository(Payment::class)->findOneByUuid($payment->getUuid()->toRfc4122());

        // Both channels report the same payment and status: the dedup must keep a single status row.
        self::assertCount(1, $payment->getStatuses());
        self::assertSame(PaymentStatusEnum::CONFIRMED, $payment->status);
        self::assertSame(FakeWorldlineConnection::PAYMENT_ID, $payment->worldlinePaymentId);
    }

    public function testALateIntermediateEventDoesNotReopenACapturedPayment(): void
    {
        $payment = $this->createPendingPayment();
        $inscriptionUuid = $payment->inscription->getUuid()->toRfc4122();

        $this->openCheckout($payment);
        $this->postWebhook($this->webhookBody($payment));

        // Same payment, earlier state, delivered late: Worldline guarantees no ordering, and the dedup does not catch
        // it since the status code differs.
        $this->connection->statusCode = 91;
        $this->connection->status = 'CAPTURE_REQUESTED';
        $this->postWebhook($this->webhookBody($payment));

        $this->em->clear();
        $payment = $this->getRepository(Payment::class)->findOneByUuid($payment->getUuid()->toRfc4122());
        $inscription = $this->eventInscriptionRepository->findOneBy(['uuid' => $inscriptionUuid]);

        // The event is recorded, but the capture stands.
        self::assertCount(2, $payment->getStatuses());
        self::assertSame(PaymentStatusEnum::CONFIRMED, $payment->status);
        self::assertSame(PaymentStatusEnum::CONFIRMED, $inscription->paymentStatus);

        // The one that costs money: a payer must never be offered a second checkout for an already captured payment.
        $this->client->request('GET', $this->paymentProcessUrl($payment));
        self::assertResponseRedirects();
    }

    private function createPendingPayment(string $email = 'worldline-e2e@example.org'): Payment
    {
        $inscription = $this->eventInscriptionRepository->findOneBy(['status' => InscriptionStatusEnum::WAITING_PAYMENT]);

        self::assertNotNull($inscription, 'A fixture inscription waiting for payment is required.');

        $payment = new Payment(\Symfony\Component\Uid\Uuid::v4(), $inscription, 9900, $inscription->packageValues ?? []);
        $inscription->addPayment($payment);

        $this->em->persist($payment);
        $this->em->flush();

        return $payment;
    }

    private function openCheckout(Payment $payment): void
    {
        $this->client->request('GET', $this->paymentProcessUrl($payment));
        $this->client->submitForm('Continuer vers ma banque');
        $this->em->clear();
    }

    private function paymentProcessUrl(Payment $payment): string
    {
        return $this->generateUrl('app_national_event_payment', [
            'slug' => $payment->inscription->event->getSlug(),
            'uuid' => $payment->getUuid()->toRfc4122(),
        ]);
    }

    private function paymentStatusUrl(Payment $payment, string $inscriptionUuid): string
    {
        return $this->generateUrl('app_national_event_payment_status', [
            'slug' => $payment->inscription->event->getSlug(),
            'uuid' => $inscriptionUuid,
            'payment' => $payment->getUuid()->toRfc4122(),
        ]);
    }

    private function generateUrl(string $route, array $parameters): string
    {
        return static::getContainer()->get('router')->generate($route, $parameters);
    }

    private function webhookBody(Payment $payment, ?string $merchantId = null): string
    {
        return json_encode([
            'apiVersion' => 'v1',
            'created' => '2026-07-16T10:00:00.000+02:00',
            'id' => 'evt-'.$payment->getUuid()->toRfc4122(),
            'merchantId' => $merchantId ?? $this->merchantId(),
            'type' => 'payment.captured',
            'payment' => $this->connection->buildPayment(),
        ], \JSON_THROW_ON_ERROR);
    }

    private function postWebhook(string $body, ?string $signature = null, ?string $urlKey = null): void
    {
        $this->client->request(
            'POST',
            '/worldline/'.($urlKey ?? self::WEBHOOK_URL_KEY),
            [],
            [],
            [
                'HTTP_HOST' => static::getContainer()->getParameter('webhook_renaissance_host'),
                'HTTP_X_GCS_KEYID' => self::WEBHOOK_ID,
                'HTTP_X_GCS_SIGNATURE' => $signature ?? base64_encode(hash_hmac('sha256', $body, self::WEBHOOK_SECRET, true)),
                'CONTENT_TYPE' => 'application/json',
            ],
            $body
        );
    }

    private function assertPaymentUntouched(Payment $payment): void
    {
        $this->em->clear();
        $payment = $this->getRepository(Payment::class)->findOneByUuid($payment->getUuid()->toRfc4122());

        self::assertCount(0, $payment->getStatuses());
        self::assertSame(PaymentStatusEnum::PENDING, $payment->status);
    }

    /** @return array<int, array> */
    private function createCheckoutRequests(): array
    {
        return array_values(array_filter(
            $this->connection->requests,
            static fn (array $request): bool => 'POST' === $request['method'] && str_contains($request['uri'], '/hostedcheckouts')
        ));
    }

    private function firstCreateCheckoutRequest(): array
    {
        $requests = $this->createCheckoutRequests();

        self::assertNotEmpty($requests, 'No checkout creation reached the Worldline SDK.');

        return json_decode($requests[0]['body'], true, 512, \JSON_THROW_ON_ERROR);
    }

    private function merchantId(): string
    {
        return $_ENV['WORLDLINE_MERCHANT_ID'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Keep one container across requests, otherwise each request would get a fresh fake connection and lose the
        // requests recorded by the previous one.
        $this->client->disableReboot();

        $this->em = $this->getEntityManager(EventInscription::class);
        $this->eventInscriptionRepository = $this->getRepository(EventInscription::class);
        $this->connection = static::getContainer()->get(Connection::class);
        $this->connection->reset();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }

    protected function tearDown(): void
    {
        $this->connection?->reset();

        parent::tearDown();

        $this->em = null;
        $this->eventInscriptionRepository = null;
        $this->connection = null;
    }
}
