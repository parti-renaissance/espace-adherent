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
 */
#[UniqueRenaissanceNewsletter]
class SubscriptionRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    #[Groups(['newsletter:write'])]
    #[Assert\NotBlank]
    public ?string $postalCode = null;

    /**
     * @StrictEmail(dnsCheck=false)
     */
    #[Groups(['newsletter:write'])]
    #[Assert\NotBlank]
    public ?string $email = null;

    #[Groups(['newsletter:write'])]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: 'getValidSources')]
    public ?string $source = null;

    #[Groups(['newsletter:write'])]
    #[Assert\IsTrue]
    public ?bool $cguAccepted = false;

    public function getValidSources(): array
    {
        return [
            NewsletterTypeEnum::SITE_RENAISSANCE,
            NewsletterTypeEnum::SITE_EU,
            NewsletterTypeEnum::SITE_ENSEMBLE,
        ];
    }
}
