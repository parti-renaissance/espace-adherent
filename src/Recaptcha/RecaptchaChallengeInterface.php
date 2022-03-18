<?php

namespace App\Recaptcha;

interface RecaptchaChallengeInterface
{
    public function getRecaptcha(): ?string;

    public function getRecaptchaSiteKey(): ?string;
}
