<?php

declare(strict_types=1);

namespace App\Membership\Signup\Request;

use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\CguAccepted as AssertCguAccepted;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\StrictEmail;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertRecaptcha]
#[Assert\Expression(
    'not this.smsOptIn or this.phone',
    message: 'Vous avez accepté de recevoir des informations par SMS, mais vous n\'avez pas précisé votre numéro de téléphone.'
)]
class SignupRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    #[Assert\NotBlank]
    #[Groups(['signup:write'])]
    #[StrictEmail(dnsCheck: false, disabledEmail: false)]
    public ?string $email = null;

    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[Groups(['signup:write'])]
    public ?string $source = null;

    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.invalid_choice')]
    #[Groups(['signup:write'])]
    public ?string $civility = null;

    #[Assert\Length(max: 50)]
    #[Groups(['signup:write'])]
    public ?string $firstName = null;

    #[Assert\Length(max: 50)]
    #[Groups(['signup:write'])]
    public ?string $lastName = null;

    #[AssertPhoneNumber]
    #[Groups(['signup:write'])]
    public ?PhoneNumber $phone = null;

    #[Assert\Length(max: 150)]
    #[Groups(['signup:write'])]
    public ?string $address = null;

    #[Assert\Length(max: 15)]
    #[Groups(['signup:write'])]
    public ?string $postalCode = null;

    #[Assert\Length(max: 255)]
    #[Groups(['signup:write'])]
    public ?string $cityName = null;

    #[Assert\Country]
    #[Groups(['signup:write'])]
    public ?string $country = null;

    #[Groups(['signup:write'])]
    public bool $emailOptIn = false;

    #[Groups(['signup:write'])]
    public bool $smsOptIn = false;

    #[AssertCguAccepted]
    #[Groups(['signup:write'])]
    public bool $cguAccepted = false;
}
