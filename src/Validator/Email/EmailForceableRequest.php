<?php

declare(strict_types=1);

namespace App\Validator\Email;

/**
 * Implemented by request DTOs that support explicit email-typo bypass.
 *
 * When isEmailForced() returns true, EmailTypoValidation is skipped — used after
 * the user has acknowledged a suggestion and chosen to keep the original email.
 */
interface EmailForceableRequest
{
    public function isEmailForced(): bool;
}
