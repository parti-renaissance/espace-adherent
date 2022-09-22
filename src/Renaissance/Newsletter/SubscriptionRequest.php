<?php

namespace App\Renaissance\Newsletter;

use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\UniqueRenaissanceNewsletter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertRecaptcha
 * @UniqueRenaissanceNewsletter
 */
class SubscriptionRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    /**
     * @Assert\NotBlank
     */
    public ?string $firstName = null;

    /**
     * @Assert\Expression("this.postalCode || this.country")
     */
    public ?string $postalCode = null;

    /**
     * @Assert\NotBlank
     */
    public ?string $country = null;

    /**
     * @Assert\NotBlank
     * @Assert\Email
     */
    public ?string $email = null;

    /**
     * @Assert\NotBlank
     * @Assert\IsTrue
     */
    public ?bool $conditions = null;

    /**
     * @Assert\NotBlank
     * @Assert\IsTrue
     */
    public ?bool $cguAccepted = null;

    public static function createFromRecaptcha(?string $recaptchaResponse): self
    {
        $object = new self();
        $object->recaptcha = $recaptchaResponse;

        return $object;
    }
}
