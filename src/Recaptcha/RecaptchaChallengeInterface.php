<?php

declare(strict_types=1);

namespace App\Recaptcha;

interface RecaptchaChallengeInterface
{
    public function getRecaptcha(): ?string;

    public function getRecaptchaSiteKey(): ?string;

    /**
     * Optional per-request override of the captcha API client name used for verification.
     * When null, the validator falls back to the default defined on the `Recaptcha` constraint.
     */
    public function getRecaptchaApi(): ?string;
}
