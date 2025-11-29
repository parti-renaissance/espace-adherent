<?php

declare(strict_types=1);

namespace App\Recaptcha;

interface RecaptchaChallengeInterface
{
    public function getRecaptcha(): ?string;

    public function getRecaptchaSiteKey(): ?string;
}
