<?php

declare(strict_types=1);

namespace Tests\App\Test\Chatbot;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;

class DummyAgent implements AgentInterface
{
    private static ?ResultInterface $nextResult = null;
    private static ?\Throwable $nextException = null;
    private static array $calls = [];

    public static function willReturn(ResultInterface $result): void
    {
        self::$nextResult = $result;
        self::$nextException = null;
    }

    public static function willThrow(\Throwable $exception): void
    {
        self::$nextException = $exception;
        self::$nextResult = null;
    }

    public static function reset(): void
    {
        self::$nextResult = null;
        self::$nextException = null;
        self::$calls = [];
    }

    public static function getCalls(): array
    {
        return self::$calls;
    }

    public function getName(): string
    {
        return 'dummy';
    }

    public function call(MessageBag $messages, array $options = []): ResultInterface
    {
        self::$calls[] = $messages;

        if (self::$nextException) {
            throw self::$nextException;
        }

        return self::$nextResult ?? new TextResult('');
    }
}
