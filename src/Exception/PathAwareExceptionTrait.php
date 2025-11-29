<?php

declare(strict_types=1);

namespace App\Exception;

trait PathAwareExceptionTrait
{
    private $path;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, ?string $path = null)
    {
        parent::__construct($message, $code, $previous);

        $this->path = $path;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }
}
