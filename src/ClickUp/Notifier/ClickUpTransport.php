<?php

declare(strict_types=1);

namespace App\ClickUp\Notifier;

use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClickUpTransport extends AbstractTransport
{
    protected const HOST = 'api.clickup.com';

    public function __construct(
        #[\SensitiveParameter] private readonly string $token,
        private readonly ?string $workspaceId = null,
        ?HttpClientInterface $client = null,
        ?EventDispatcherInterface $dispatcher = null,
    ) {
        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return \sprintf('clickup://%s', $this->getEndpoint());
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ChatMessage && $message->getOptions() instanceof ClickUpOptions;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof ChatMessage || !$message->getOptions() instanceof ClickUpOptions) {
            throw new UnsupportedMessageTypeException(__CLASS__, ChatMessage::class, $message);
        }

        if (null === $this->workspaceId) {
            throw new LogicException('A "workspace_id" DSN option is required to post ClickUp messages.');
        }

        $channelId = $message->getRecipientId();

        if (null === $channelId) {
            throw new LogicException('A recipient (channel id) is required to post a ClickUp message.');
        }

        $response = $this->client->request(
            'POST',
            \sprintf('https://%s/api/v3/workspaces/%s/chat/channels/%s/messages', $this->getEndpoint(), $this->workspaceId, $channelId),
            [
                'headers' => ['Authorization' => $this->token],
                'json' => ['type' => 'message', 'content' => $message->getSubject()],
            ],
        );

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the ClickUp API.', $response, 0, $e);
        }

        if ($statusCode >= 400) {
            throw new TransportException(\sprintf('Unable to call the ClickUp API (HTTP %d).', $statusCode), $response);
        }

        return new SentMessage($message, (string) $this);
    }
}
