<?php

declare(strict_types=1);

namespace App\Chatbot\Antiseche\Exception;

class AntisecheException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $statusCode = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
