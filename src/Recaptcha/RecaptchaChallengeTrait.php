<?php

namespace App\Recaptcha;

trait RecaptchaChallengeTrait
{
    protected ?string $recaptcha = null;
    protected ?string $recaptchaSiteKey = null;

    public function getRecaptcha(): ?string
    {
        return $this->recaptcha;
    }

    public function setRecaptcha(?string $recaptcha): void
    {
        $this->recaptcha = $recaptcha;
    }

    public function getRecaptchaSiteKey(): ?string
    {
        return $this->recaptchaSiteKey;
    }

    public function setRecaptchaSiteKey(?string $recaptchaSiteKey): void
    {
        $this->recaptchaSiteKey = $recaptchaSiteKey;
    }
}
