<?php

namespace App\Adhesion\Request;

use App\Procuration\V2\InitialRequestTypeEnum;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha;
use App\Validator\StrictEmail;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Recaptcha(api="friendly_captcha", groups={"adhesion-email:persist"})
 */
class EmailValidationRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    /**
     * @Assert\NotBlank(groups={"Default", "adhesion-email:persist", "procuration-email:persist", "bde-email:persist"}, message="L'adresse email est nécessaire pour continuer.")
     * @StrictEmail(captainVerifyCheck=true, groups={"Default"})
     * @StrictEmail(dnsCheck=false, groups={"adhesion-email:persist", "procuration-email:persist", "bde-email:persist"})
     */
    #[Groups(['adhesion-email:validate', 'adhesion-email:persist', 'procuration-email:persist', 'bde-email:validate', 'bde-email:persist'])]
    private ?string $email = null;

    /**
     * @Assert\NotBlank(groups={"procuration-email:persist"})
     */
    #[Groups(['procuration-email:persist'])]
    public ?InitialRequestTypeEnum $type = null;

    #[Groups(['adhesion-email:validate', 'bde-email:validate'])]
    public ?string $token = null;

    public function setEmail(?string $email): void
    {
        $this->email = strtolower($email);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
