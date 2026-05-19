<?php

declare(strict_types=1);

namespace App\Chatbot\Antiseche;

use App\Chatbot\Antiseche\Exception\AntisecheException;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AntisecheClient
{
    private const STREAM_ENDPOINT = '/api/bot/stream';

    public function __construct(
        private readonly HttpClientInterface $antisecheClient,
    ) {
    }

    /** @return iterable<string> */
    public function openStream(string $message, MessageBag $context): iterable
    {
        try {
            $response = $this->antisecheClient->request('POST', self::STREAM_ENDPOINT, [
                'json' => [
                    'message' => $message,
                    'history' => $this->serializeHistory($context),
                ],
                'buffer' => false,
            ]);
            $status = $response->getStatusCode();
        } catch (HttpExceptionInterface $exception) {
            throw new AntisecheException('Antiseche stream is unreachable.', previous: $exception);
        }

        if (200 !== $status) {
            throw new AntisecheException(\sprintf('Antiseche stream returned HTTP %d.', $status), statusCode: $status);
        }

        return $this->yieldStreamChunks($response);
    }

    /**
     * @return \Generator<int, string>
     */
    private function yieldStreamChunks(ResponseInterface $response): \Generator
    {
        foreach ($this->antisecheClient->stream($response) as $chunk) {
            $content = $chunk->getContent();
            if ('' !== $content) {
                yield $content;
            }
        }
    }

    /** @return list<array{role: string, content: string}> */
    private function serializeHistory(MessageBag $bag): array
    {
        $history = [];

        foreach ($bag as $msg) {
            $role = match (true) {
                $msg instanceof UserMessage => 'user',
                $msg instanceof AssistantMessage => 'assistant',
                default => null,
            };

            if (null === $role) {
                continue;
            }

            $content = $this->extractContent($msg);
            if ('' === $content) {
                continue;
            }

            $history[] = ['role' => $role, 'content' => $content];
        }

        return $history;
    }

    private function extractContent(UserMessage|AssistantMessage $msg): string
    {
        if ($msg instanceof AssistantMessage) {
            return trim((string) $msg->getContent());
        }

        $text = '';
        foreach ($msg->getContent() as $part) {
            if ($part instanceof Text) {
                $text .= $part->getText();
            }
        }

        return trim($text);
    }
}
