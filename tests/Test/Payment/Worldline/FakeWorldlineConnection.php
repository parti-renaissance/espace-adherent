<?php

declare(strict_types=1);

namespace Tests\App\Test\Payment\Worldline;

use OnlinePayments\Sdk\Communication\Connection;
use OnlinePayments\Sdk\Logging\CommunicatorLogger;

/**
 * Replaces the SDK cURL connection in tests.
 *
 * Faking at this depth keeps the whole SDK chain exercised for real — request building, JSON serialisation, signing,
 * response parsing — while never touching the network. The checkout creation is remembered so that the payment
 * payloads echo back the merchant reference and the amount, as Worldline does.
 */
class FakeWorldlineConnection implements Connection
{
    public const HOSTED_CHECKOUT_ID = 'HC-TEST-0001';
    public const PAYMENT_ID = '3066749753_0';
    public const REDIRECT_URL = 'https://payment.preprod.direct.worldline-solutions.com/hostedcheckout/HC-TEST-0001';
    public const RETURN_MAC = 'test-return-mac';

    /** @var array<int, array{method: string, uri: string, body: mixed}> */
    public array $requests = [];

    public int $statusCode = 9;
    public string $status = 'CAPTURED';
    public string $paymentId = self::PAYMENT_ID;

    /** Forces the payment payload amount, to simulate a tampered or mismatching notification. */
    public ?int $amountOverride = null;
    public ?string $currencyOverride = null;

    private ?string $merchantReference = null;
    private ?int $amount = null;

    public function get(string $requestUri, array $requestHeaders, callable $responseHandler): void
    {
        $this->record('GET', $requestUri, null);

        if (str_contains($requestUri, '/hostedcheckouts/')) {
            $this->respond($responseHandler, ['createdPaymentOutput' => ['payment' => $this->buildPayment()], 'status' => 'PAYMENT_CREATED']);

            return;
        }

        $this->respond($responseHandler, $this->buildPayment());
    }

    public function post(string $requestUri, array $requestHeaders, $body, callable $responseHandler): void
    {
        $this->record('POST', $requestUri, $body);

        $request = \is_string($body) ? json_decode($body, true) : [];
        $this->merchantReference = $request['order']['references']['merchantReference'] ?? null;
        $this->amount = $request['order']['amountOfMoney']['amount'] ?? null;

        $this->respond($responseHandler, [
            'RETURNMAC' => self::RETURN_MAC,
            'hostedCheckoutId' => self::HOSTED_CHECKOUT_ID,
            'merchantReference' => $this->merchantReference,
            'redirectUrl' => self::REDIRECT_URL,
        ]);
    }

    public function delete(string $requestUri, array $requestHeaders, callable $responseHandler): void
    {
        $this->record('DELETE', $requestUri, null);
        $this->respond($responseHandler, []);
    }

    public function put(string $requestUri, array $requestHeaders, $body, callable $responseHandler): void
    {
        $this->record('PUT', $requestUri, $body);
        $this->respond($responseHandler, []);
    }

    public function enableLogging(CommunicatorLogger $communicatorLogger): void
    {
    }

    public function disableLogging(): void
    {
    }

    /**
     * Pretends a checkout was already opened, for the flows that resolve a payment without creating it first.
     */
    public function primeCheckout(string $merchantReference, int $amount): void
    {
        $this->merchantReference = $merchantReference;
        $this->amount = $amount;
    }

    public function reset(): void
    {
        $this->requests = [];
        $this->statusCode = 9;
        $this->status = 'CAPTURED';
        $this->paymentId = self::PAYMENT_ID;
        $this->amountOverride = null;
        $this->currencyOverride = null;
        $this->merchantReference = null;
        $this->amount = null;
    }

    public function buildPayment(): array
    {
        return [
            'id' => $this->paymentId,
            'paymentOutput' => [
                'amountOfMoney' => [
                    'amount' => $this->amountOverride ?? $this->amount ?? 0,
                    'currencyCode' => $this->currencyOverride ?? 'EUR',
                ],
                'references' => ['merchantReference' => $this->merchantReference],
            ],
            'status' => $this->status,
            'statusOutput' => ['statusCode' => $this->statusCode],
        ];
    }

    private function record(string $method, string $uri, mixed $body): void
    {
        $this->requests[] = ['method' => $method, 'uri' => $uri, 'body' => $body];
    }

    private function respond(callable $responseHandler, array $payload): void
    {
        $responseHandler(200, json_encode($payload, \JSON_THROW_ON_ERROR), ['Content-Type' => 'application/json']);
    }
}
