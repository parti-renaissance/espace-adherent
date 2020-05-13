<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BadUuidRequestException extends BadRequestHttpException
{
    public function __construct(InvalidUuidException $previous, string $message = 'Invalid uuid.', int $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}
