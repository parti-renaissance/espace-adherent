<?php

declare(strict_types=1);

namespace Tests\App\Test\Chatbot;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;

class DummyAntisecheAgent implements AgentInterface
{
    private static ?ResultInterface $nextResult = null;
    private static ?\Throwable $nextException = null;

    public function __construct(mixed ...$ignored)
    {
    }

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
    }

    public function getName(): string
    {
        return 'antiseche';
    }

    public function call(MessageBag $messages, array $options = []): ResultInterface
    {
        if (self::$nextException) {
            throw self::$nextException;
        }

        return self::$nextResult ?? new TextResult('');
    }
}
