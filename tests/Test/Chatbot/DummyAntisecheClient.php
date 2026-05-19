<?php

declare(strict_types=1);

namespace Tests\App\Test\Chatbot;

use App\Chatbot\Antiseche\AntisecheClient;
use App\Chatbot\Antiseche\Exception\AntisecheException;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\HttpClient\MockHttpClient;

class DummyAntisecheClient extends AntisecheClient
{
    /** @var list<string>|null */
    private static ?array $nextStreamChunks = null;
    private static ?AntisecheException $nextException = null;

    public function __construct()
    {
        parent::__construct(new MockHttpClient());
    }

    /** @param list<string> $chunks */
    public static function willStreamChunks(array $chunks): void
    {
        self::$nextStreamChunks = $chunks;
        self::$nextException = null;
    }

    public static function willThrow(AntisecheException $exception): void
    {
        self::$nextException = $exception;
        self::$nextStreamChunks = null;
    }

    public static function reset(): void
    {
        self::$nextStreamChunks = null;
        self::$nextException = null;
    }

    public function openStream(string $message, MessageBag $context): iterable
    {
        if (self::$nextException) {
            throw self::$nextException;
        }

        return self::yieldChunks(self::$nextStreamChunks ?? []);
    }

    /**
     * @param list<string> $chunks
     *
     * @return \Generator<int, string>
     */
    private static function yieldChunks(array $chunks): \Generator
    {
        foreach ($chunks as $chunk) {
            yield $chunk;
        }
    }
}
