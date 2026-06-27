<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Webhook;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Tests\App\AbstractWebTestCase;

class SentryWebhookControllerTest extends AbstractWebTestCase
{
    private const KEY = 'test-sentry-key';

    protected function setUp(): void
    {
        $_SERVER['SENTRY_WEBHOOK_SECRET'] = $_ENV['SENTRY_WEBHOOK_SECRET'] = self::KEY;

        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('webhook_renaissance_host'));
    }

    protected function tearDown(): void
    {
        unset($_SERVER['SENTRY_WEBHOOK_SECRET'], $_ENV['SENTRY_WEBHOOK_SECRET']);

        parent::tearDown();
    }

    public function testValidKeyDispatchesCommand(): void
    {
        $this->post(self::KEY, $this->payload());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertCount(1, $this->dispatchedMessages());
    }

    public function testInvalidKeyReturns401AndDoesNotDispatch(): void
    {
        $this->post('wrong-key', $this->payload());

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertCount(0, $this->dispatchedMessages());
    }

    public function testMalformedPayloadIsAcknowledgedWithoutDispatch(): void
    {
        $this->post(self::KEY, json_encode(['data' => ['not_an_event' => true]]));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertCount(0, $this->dispatchedMessages());
    }

    private function post(string $key, string $body): void
    {
        $this->client->request(Request::METHOD_POST, '/sentry/'.$key, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $body);
    }

    private function payload(): string
    {
        return json_encode(['data' => ['event' => [
            'project' => 4511585443381328,
            'platform' => 'php',
            'environment' => 'production',
            'issue_id' => '42',
            'title' => 'Boom',
        ]]]);
    }

    /**
     * @return array<int, mixed>
     */
    private function dispatchedMessages(): array
    {
        /** @var InMemoryTransport $transport */
        $transport = static::getContainer()->get('messenger.transport.test_async');

        return $transport->getSent();
    }
}
