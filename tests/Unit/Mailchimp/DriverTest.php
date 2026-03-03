<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp;

use App\Mailchimp\Driver;
use App\Mailchimp\Exception\FailedSyncException;
use App\Mailchimp\Exception\InvalidPayloadException;
use App\Mailchimp\Exception\RemovedContactStatusException;
use App\Mailchimp\Exception\SmsPhoneAlreadySubscribedException;
use App\Mailchimp\Synchronisation\Request\ContactRequest;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class DriverTest extends TestCase
{
    private HttpClientInterface&Stub $httpClient;
    private Driver $driver;

    protected function setUp(): void
    {
        $this->httpClient = $this->createStub(HttpClientInterface::class);
        $this->driver = new Driver($this->httpClient, 'list-id');
        $this->driver->setLogger(new NullLogger());
    }

    public function testAddContactOnRemovedContactErrorThrowsRemovedContactStatusException(): void
    {
        $response = $this->stubResponse(400, '{"detail":"contact must re-subscribe to get back on the list"}');
        $this->httpClient->method('request')->willReturn($response);

        $this->expectException(RemovedContactStatusException::class);
        $this->expectExceptionMessage('Permanently deleted');

        $this->driver->addContact(new ContactRequest('test@example.com'), 'list-id', true);
    }

    public function testAddContactOnComplianceStateErrorThrowsRemovedContactStatusException(): void
    {
        $response = $this->stubResponse(400, '{"detail":"is already a list member in compliance state due to unsubscribe"}');
        $this->httpClient->method('request')->willReturn($response);

        $this->expectException(RemovedContactStatusException::class);
        $this->expectExceptionMessage('Unsubscribed');

        $this->driver->addContact(new ContactRequest('test@example.com'), 'list-id', true);
    }

    public function testAddContactOnTechnicalErrorWithThrowThrowsFailedSyncException(): void
    {
        $response = $this->stubResponse(500, '{"detail":"Internal Server Error"}');
        $this->httpClient->method('request')->willReturn($response);

        $this->expectException(FailedSyncException::class);

        $this->driver->addContact(new ContactRequest('test@example.com'), 'list-id', true);
    }

    public function testAddContactOnTechnicalErrorWithoutThrowReturnsNull(): void
    {
        $response = $this->stubResponse(500, '{"detail":"Internal Server Error"}');
        $this->httpClient->method('request')->willReturn($response);

        $result = $this->driver->addContact(new ContactRequest('test@example.com'), 'list-id');

        self::assertNull($result);
    }

    public function testUpdateContactOnTechnicalErrorWithThrowThrowsFailedSyncException(): void
    {
        $response = $this->stubResponse(500, '{"detail":"Internal Server Error"}');
        $this->httpClient->method('request')->willReturn($response);

        $this->expectException(FailedSyncException::class);

        $this->driver->updateContact('contact-id', new ContactRequest('test@example.com'), 'list-id', true);
    }

    public function testUpdateContactOnSuccessReturnsTrue(): void
    {
        $response = $this->stubResponse(200, '{"id":"contact-id"}');
        $this->httpClient->method('request')->willReturn($response);

        $result = $this->driver->updateContact('contact-id', new ContactRequest('test@example.com'), 'list-id');

        self::assertTrue($result);
    }

    public function testAddContactOnInvalidResourceErrorThrowsInvalidPayloadException(): void
    {
        $response = $this->stubResponse(400, '{"title":"Invalid Resource","detail":"The resource submitted could not be validated."}');
        $this->httpClient->method('request')->willReturn($response);

        $this->expectException(InvalidPayloadException::class);

        $this->driver->addContact(new ContactRequest('test@example.com'), 'list-id', true);
    }

    public function testAddContactOnInvalidPhoneErrorThrowsInvalidPayloadException(): void
    {
        $response = $this->stubResponse(400, '{"title":"Invalid Resource","detail":"Invalid phone number format"}');
        $this->httpClient->method('request')->willReturn($response);

        $this->expectException(InvalidPayloadException::class);

        $this->driver->addContact(new ContactRequest('test@example.com'), 'list-id', true);
    }

    public function testUpdateContactOnInvalidResourceErrorThrowsInvalidPayloadException(): void
    {
        $response = $this->stubResponse(400, '{"title":"Invalid Resource","detail":"The resource submitted could not be validated."}');
        $this->httpClient->method('request')->willReturn($response);

        $this->expectException(InvalidPayloadException::class);

        $this->driver->updateContact('contact-id', new ContactRequest('test@example.com'), 'list-id', true);
    }

    public function testAddContactOnSuccessReturnsContactId(): void
    {
        $response = $this->stubResponse(200, '{"id":"new-contact-id"}');
        $this->httpClient->method('request')->willReturn($response);

        $result = $this->driver->addContact(new ContactRequest('test@example.com'), 'list-id', true);

        self::assertSame('new-contact-id', $result);
    }

    public function testGetMembersReturnsArrayOfMembers(): void
    {
        $response = $this->stubResponse(200, '{"members":[{"email_address":"a@test.com","contact_id":"id-1"},{"email_address":"b@test.com","contact_id":"id-2"}]}');
        $this->httpClient->method('request')->willReturn($response);

        $result = $this->driver->getMembers('list-id', 0, 1000);

        self::assertCount(2, $result);
        self::assertSame('a@test.com', $result[0]['email_address']);
        self::assertSame('id-1', $result[0]['contact_id']);
        self::assertSame('b@test.com', $result[1]['email_address']);
        self::assertSame('id-2', $result[1]['contact_id']);
    }

    public function testGetMembersReturnsEmptyArrayOnError(): void
    {
        $response = $this->stubResponse(500, '{"detail":"Internal Server Error"}');
        $this->httpClient->method('request')->willReturn($response);

        $result = $this->driver->getMembers('list-id', 0, 1000);

        self::assertSame([], $result);
    }

    public function testAddContactOnPhoneAlreadySubscribedThrowsSmsPhoneAlreadySubscribedException(): void
    {
        $response = $this->stubResponse(400, '{"detail":"The phone number is already subscribed to another contact in this SMS program"}');
        $this->httpClient->method('request')->willReturn($response);

        $request = new ContactRequest('test@example.com');
        $request->setSmsPhone('+33612345678');

        $this->expectException(SmsPhoneAlreadySubscribedException::class);

        $this->driver->addContact($request, 'list-id', true);
    }

    public function testAddContactOnPhoneAlreadySubscribedIncludesPhoneInException(): void
    {
        $response = $this->stubResponse(400, '{"detail":"The phone number is already subscribed to another contact in this SMS program"}');
        $this->httpClient->method('request')->willReturn($response);

        $request = new ContactRequest('test@example.com');
        $request->setSmsPhone('+33612345678');

        try {
            $this->driver->addContact($request, 'list-id', true);
            self::fail('Expected SmsPhoneAlreadySubscribedException to be thrown');
        } catch (SmsPhoneAlreadySubscribedException $e) {
            self::assertSame('+33612345678', $e->phone);
        }
    }

    public function testUpdateContactOnPhoneAlreadySubscribedThrowsSmsPhoneAlreadySubscribedException(): void
    {
        $response = $this->stubResponse(400, '{"detail":"The phone number is already subscribed to another contact in this SMS program"}');
        $this->httpClient->method('request')->willReturn($response);

        $request = new ContactRequest('test@example.com');
        $request->setSmsPhone('+33698765432');

        $this->expectException(SmsPhoneAlreadySubscribedException::class);

        $this->driver->updateContact('contact-id', $request, 'list-id', true);
    }

    private function stubResponse(int $statusCode, string $content): ResponseInterface&Stub
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getContent')->willReturn($content);
        $response->method('toArray')->willReturn(json_decode($content, true) ?? []);

        return $response;
    }
}
