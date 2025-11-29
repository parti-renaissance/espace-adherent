<?php

declare(strict_types=1);

namespace App\Exception;

use App\Donation\Paybox\PayboxPaymentUnsubscriptionErrorEnum;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PayboxPaymentUnsubscriptionException extends BadRequestHttpException
{
    public function __construct(int $codeError, ?\Exception $previous = null, $code = 0)
    {
        parent::__construct(\sprintf('%d: %s', $codeError, PayboxPaymentUnsubscriptionErrorEnum::getMessage($codeError)), $previous, $code);
    }
}
