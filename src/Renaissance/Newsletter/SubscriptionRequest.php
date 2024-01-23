<?php

namespace App\Renaissance\Newsletter;

use App\Newsletter\NewsletterTypeEnum;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\StrictEmail;
use App\Validator\UniqueRenaissanceNewsletter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertRecaptcha(api="friendly_captcha")
 * @UniqueRenaissanceNewsletter
 */
class SubscriptionRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    /**
     * @Assert\NotBlank
     */
    #[Groups(['newsletter:write'])]
    public ?string $postalCode = null;

    /**
     * @Assert\NotBlank
     * @StrictEmail(dnsCheck=false)
     */
    #[Groups(['newsletter:write'])]
    public ?string $email = null;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback="getValidSources")
     */
    #[Groups(['newsletter:write'])]
    public ?string $source = null;

    /**
     * @Assert\IsTrue
     */
    #[Groups(['newsletter:write'])]
    public ?bool $cguAccepted = false;

    public function getValidSources(): array
    {
        return [
            NewsletterTypeEnum::SITE_RENAISSANCE,
            NewsletterTypeEnum::SITE_EU,
        ];
    }
}
