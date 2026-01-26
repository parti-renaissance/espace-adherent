<?php

declare(strict_types=1);

namespace App\Renaissance\Newsletter;

use App\Newsletter\NewsletterTypeEnum;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\StrictEmail;
use App\Validator\UniqueRenaissanceNewsletter;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertRecaptcha]
#[UniqueRenaissanceNewsletter]
class SubscriptionRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    #[Groups(['newsletter:write'])]
    public ?string $firstName = null;

    #[Groups(['newsletter:write'])]
    public ?string $lastName = null;

    #[Assert\NotBlank]
    #[Groups(['newsletter:write'])]
    public ?string $postalCode = null;

    #[Assert\NotBlank]
    #[Groups(['newsletter:write'])]
    #[StrictEmail(dnsCheck: false)]
    public ?string $email = null;

    #[Assert\Choice(callback: 'getValidSources')]
    #[Assert\NotBlank]
    #[Groups(['newsletter:write'])]
    public ?string $source = null;

    #[Assert\IsTrue]
    #[Groups(['newsletter:write'])]
    public ?bool $cguAccepted = false;

    public function getValidSources(): array
    {
        return [
            NewsletterTypeEnum::SITE_RENAISSANCE,
            NewsletterTypeEnum::SITE_NRP,
            NewsletterTypeEnum::SITE_EU,
            NewsletterTypeEnum::SITE_ENSEMBLE,
            NewsletterTypeEnum::SITE_STOPRESEAUX,
        ];
    }
}
