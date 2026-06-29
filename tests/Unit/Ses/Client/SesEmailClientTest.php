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

    public function testEmptyMessageIdThrowsForRetryInsteadOfSilentSuccess(): void
    {
        // A 200 without a usable MessageId means SES never really accepted the send (e.g. an unsigned
        // request when no credentials resolve): it must surface as a retryable failure, never a silent
        // "sent" that marks the recipient done without an email ever leaving.
        $httpClient = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['MessageId' => '']),
            ['http_code' => 200]
        ));

        $this->expectException(\RuntimeException::class);

        $this->createClient($httpClient)->sendEmail($this->basicEmail());
    }

    public function testConfigurationSetNameIsIncludedWhenConfigured(): void
    {
        $capturedBody = null;
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = $options['body'] ?? null;

            return new MockResponse(json_encode(['MessageId' => 'ses-msg-1']), ['http_code' => 200]);
        });

        $this->createClient($httpClient, 'renaissance-publications')->sendEmail($this->basicEmail());

        self::assertIsString($capturedBody);
        self::assertStringContainsString('renaissance-publications', $capturedBody);
    }

    public function testConfigurationSetNameIsOmittedWhenEmpty(): void
    {
        $capturedBody = null;
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = $options['body'] ?? null;

            return new MockResponse(json_encode(['MessageId' => 'ses-msg-1']), ['http_code' => 200]);
        });

        $this->createClient($httpClient)->sendEmail($this->basicEmail());

        self::assertIsString($capturedBody);
        self::assertStringNotContainsString('ConfigurationSetName', $capturedBody);
    }

    public function testListUnsubscribeHeadersIncludedWhenUrlProvided(): void
    {
        $capturedBody = null;
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = $options['body'] ?? null;

            return new MockResponse(json_encode(['MessageId' => 'ses-msg-1']), ['http_code' => 200]);
        });

        $this->createClient($httpClient)->sendEmail(new SesEmail(
            to: 'jean@example.org',
            subject: 'Sujet',
            html: '<p>x</p>',
            fromEmail: 'contact@parti-renaissance.fr',
            listUnsubscribeUrl: 'https://vox.test/desabonnement/TOKEN',
        ));

        self::assertIsString($capturedBody);
        $headers = json_decode($capturedBody, true)['Content']['Simple']['Headers'] ?? null;
        self::assertContains(['Name' => 'List-Unsubscribe', 'Value' => '<https://vox.test/desabonnement/TOKEN>'], $headers);
        self::assertContains(['Name' => 'List-Unsubscribe-Post', 'Value' => 'List-Unsubscribe=One-Click'], $headers);
    }

    public function testListUnsubscribeHeadersOmittedWhenUrlNull(): void
    {
        $capturedBody = null;
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = $options['body'] ?? null;

            return new MockResponse(json_encode(['MessageId' => 'ses-msg-1']), ['http_code' => 200]);
        });

        $this->createClient($httpClient)->sendEmail($this->basicEmail());

        self::assertIsString($capturedBody);
        self::assertStringNotContainsString('List-Unsubscribe', $capturedBody);
    }

    public function testEmailTagsIncludedWhenCorrelationUuidsProvided(): void
    {
        $capturedBody = null;
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = $options['body'] ?? null;

            return new MockResponse(json_encode(['MessageId' => 'ses-msg-1']), ['http_code' => 200]);
        });

        $this->createClient($httpClient)->sendEmail(new SesEmail(
            to: 'jean@example.org',
            subject: 'Sujet',
            html: '<p>x</p>',
            fromEmail: 'contact@parti-renaissance.fr',
            campaignUuid: '11111111-1111-4111-8111-111111111111',
            adherentUuid: '22222222-2222-4222-8222-222222222222',
        ));

        self::assertIsString($capturedBody);
        $tags = json_decode($capturedBody, true)['EmailTags'] ?? null;
        self::assertContains(['Name' => 'campaign_uuid', 'Value' => '11111111-1111-4111-8111-111111111111'], $tags);
        self::assertContains(['Name' => 'adherent_uuid', 'Value' => '22222222-2222-4222-8222-222222222222'], $tags);
    }

    public function testEmailTagsOmittedWhenCorrelationUuidsNull(): void
    {
        $capturedBody = null;
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$capturedBody): MockResponse {
            $capturedBody = $options['body'] ?? null;

            return new MockResponse(json_encode(['MessageId' => 'ses-msg-1']), ['http_code' => 200]);
        });

        $this->createClient($httpClient)->sendEmail($this->basicEmail());

        self::assertIsString($capturedBody);
        self::assertStringNotContainsString('EmailTags', $capturedBody);
    }

    private function createClient(HttpClientInterface $httpClient, ?string $configurationSetName = null): SesEmailClient
    {
        return new SesEmailClient(new SesClient(
            ['region' => 'eu-west-3', 'accessKeyId' => 'test-key', 'accessKeySecret' => 'test-secret'],
            null,
            $httpClient,
        ), $configurationSetName);
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
