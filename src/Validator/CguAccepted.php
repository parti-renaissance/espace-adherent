<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\IsTrueValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class CguAccepted extends IsTrue
{
    public string $message = 'common.cgu.not_accepted';

    public function validatedBy(): string
    {
        return IsTrueValidator::class;
    }
}
