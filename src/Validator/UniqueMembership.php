<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueMembership extends Constraint
{
    public string $message = 'adherent.email_address.not_unique';
    public string $path = 'emailAddress';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
