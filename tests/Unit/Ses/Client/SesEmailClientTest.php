<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Client;

use App\Ses\Client\SesEmail;
use App\Ses\Client\SesEmailClient;
use AsyncAws\Core\Exception\Http\ClientException;
use AsyncAws\Ses\SesClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Drives the real AsyncAws SesClient with a MockHttpClient so the test exercises the actual request
 * building and async-aws error classification, not a mocked Result.
 */
class SesEmailClientTest extends TestCase
{
    public function testSendEmailReturnsSentOutcomeWithMessageId(): void
    {
        $capturedBody = null;
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = $options['body'] ?? null;

            return new MockResponse(json_encode(['MessageId' => 'ses-msg-123']), ['http_code' => 200]);
        });

        $outcome = $this->createClient($httpClient)->sendEmail(new SesEmail(
            to: 'jean@example.org',
            subject: 'Sujet test',
            html: '<p>Bonjour Jean</p>',
            fromEmail: 'contact@parti-renaissance.fr',
            fromName: 'Renaissance',
            replyTo: 'repondre@parti-renaissance.fr',
        ));

        self::assertTrue($outcome->isSent());
        self::assertSame('ses-msg-123', $outcome->messageId);
        // Request is built as expected: recipient, formatted from, and rendered HTML are in the payload.
        self::assertIsString($capturedBody);
        self::assertStringContainsString('jean@example.org', $capturedBody);
        self::assertStringContainsString('Renaissance <contact@parti-renaissance.fr>', $capturedBody);
        self::assertStringContainsString('Bonjour Jean', $capturedBody);
    }

    public function testPermanentRejectReturnsRejectedOutcome(): void
    {
        $httpClient = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['message' => 'Email address is not verified.']),
            ['http_code' => 400]
        ));

        $outcome = $this->createClient($httpClient)->sendEmail($this->basicEmail());

        self::assertFalse($outcome->isSent());
        self::assertNotNull($outcome->rejectionReason);
    }

    public function testThrottlingPropagatesAsExceptionForRetry(): void
    {
        $httpClient = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['message' => 'Too many requests.']),
            ['http_code' => 429]
        ));

        $this->expectException(ClientException::class);

        $this->createClient($httpClient)->sendEmail($this->basicEmail());
    }

    private function createClient(HttpClientInterface $httpClient): SesEmailClient
    {
        return new SesEmailClient(new SesClient(
            ['region' => 'eu-west-3', 'accessKeyId' => 'test-key', 'accessKeySecret' => 'test-secret'],
            null,
            $httpClient,
        ));
    }

    private function basicEmail(): SesEmail
    {
        return new SesEmail(
            to: 'jean@example.org',
            subject: 'Sujet',
            html: '<p>x</p>',
            fromEmail: 'contact@parti-renaissance.fr',
        );
    }
}
