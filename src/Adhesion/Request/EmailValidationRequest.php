<?php

declare(strict_types=1);

namespace App\Adhesion\Request;

use App\Procuration\InitialRequestTypeEnum;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\StrictEmail;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertRecaptcha(groups: ['adhesion-email:persist'])]
class EmailValidationRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    #[Assert\NotBlank(message: "L'adresse email est nécessaire pour continuer.", groups: ['Default', 'adhesion-email:persist', 'procuration-email:persist', 'bde-email:persist'])]
    #[Groups(['adhesion-email:validate', 'adhesion-email:persist', 'procuration-email:persist', 'bde-email:validate', 'bde-email:persist'])]
    #[StrictEmail(groups: ['Default'])]
    #[StrictEmail(dnsCheck: false, groups: ['adhesion-email:persist', 'procuration-email:persist', 'bde-email:persist'])]
    private ?string $email = null;

    #[Assert\NotBlank(groups: ['procuration-email:persist'])]
    #[Groups(['procuration-email:persist'])]
    public ?InitialRequestTypeEnum $type = null;

    #[Groups(['adhesion-email:validate', 'bde-email:validate'])]
    public ?string $token = null;

    public function setEmail(?string $email): void
    {
        $this->email = mb_strtolower(trim($email));
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
