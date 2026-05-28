<?php

declare(strict_types=1);

namespace App\Validator\Email\Reason;

use Egulias\EmailValidator\Result\Reason\EmptyReason;

/**
 * Transports the suggested correction up to StrictEmailValidator, which serializes it
 * into the violation parameters so the front can offer "Did you mean X?" UX.
 */
class EmailTypoReason extends EmptyReason
{
    public function __construct(public readonly string $suggestion)
    {
    }
}
