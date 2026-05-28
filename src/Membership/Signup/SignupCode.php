<?php

declare(strict_types=1);

namespace App\Membership\Signup;

class SignupCode
{
    /** Number of digits in the signup confirmation code. */
    public const LENGTH = 3;

    /** Validation pattern derived from LENGTH — keep both sides coupled. */
    public const PATTERN = '/^\d{'.self::LENGTH.'}$/';
}
