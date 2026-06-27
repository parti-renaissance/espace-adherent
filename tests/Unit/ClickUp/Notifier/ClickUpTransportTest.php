<?php

declare(strict_types=1);

namespace Tests\App\Unit\ClickUp\Notifier;

use App\ClickUp\Notifier\ClickUpOptions;
use App\ClickUp\Notifier\ClickUpTransport;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\ChatMessage;

#[Group('unit')]
class ClickUpTransportTest extends TestCase
{
    public function testSendPostsChatMessageToChannel(): void
    {
        $response = new MockResponse('{"id":"msg_1"}', ['http_code' => 200]);
        $transport = new ClickUpTransport('pk_token', 'WS123', new MockHttpClient($response));

        $transport->send(new ChatMessage('Hello world', new ClickUpOptions('CH456')));

        self::assertSame('POST', $response->getRequestMethod());
        self::assertSame('https://api.clickup.com/api/v3/workspaces/WS123/chat/channels/CH456/messages', $response->getRequestUrl());

        $options = $response->getRequestOptions();
        self::assertStringContainsString('"content":"Hello world"', $options['body']);
        self::assertStringContainsString('"type":"message"', $options['body']);
        self::assertStringContainsStringIgnoringCase('authorization: pk_token', implode("\n", $options['headers']));
    }

    public function testSendThrowsTransportExceptionOnApiError(): void
    {
        $transport = new ClickUpTransport('pk_token', 'WS123', new MockHttpClient(new MockResponse('{"err":"unauthorized"}', ['http_code' => 401])));

        $this->expectException(TransportException::class);

        $transport->send(new ChatMessage('Hello world', new ClickUpOptions('CH456')));
    }
}
