<?php

namespace App\Recaptcha;

use Symfony\Component\Serializer\Annotation\Groups;

trait RecaptchaChallengeTrait
{
    /**
     * @Groups({"contact_create", "legislative_newsletter_subscriptions_write"})
     */
    protected ?string $recaptcha = null;

    /**
     * @Groups({"contact_create", "legislative_newsletter_subscriptions_write"})
     */
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
