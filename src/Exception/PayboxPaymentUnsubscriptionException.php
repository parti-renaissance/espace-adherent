<?php

namespace App\Exception;

use App\Donation\PayboxPaymentUnsubscriptionErrorEnum;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PayboxPaymentUnsubscriptionException extends BadRequestHttpException
{
    private $codeError;

    public function __construct(int $codeError, \Exception $previous = null, $code = 0)
    {
        $this->codeError = $codeError;

        parent::__construct(sprintf('%d: %s', $codeError, PayboxPaymentUnsubscriptionErrorEnum::getMessage($codeError)), $previous, $code);
    }

    public function getCodeError(): int
    {
        return $this->codeError;
    }
}
