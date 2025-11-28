<?php

declare(strict_types=1);

namespace App\Renaissance\Petition;

use App\Enum\CivilityEnum;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\StrictEmail;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertRecaptcha]
class SignatureRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    #[Assert\Choice(callback: [CivilityEnum::class, 'getAsArray'])]
    #[Assert\NotBlank]
    #[Groups(['petition:write'])]
    public ?string $civility = null;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['petition:write'])]
    public ?string $firstName = null;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['petition:write'])]
    public ?string $lastName = null;

    #[Assert\NotBlank]
    #[Groups(['petition:write'])]
    #[StrictEmail(dnsCheck: false)]
    public ?string $email = null;

    #[Assert\Length(max: 10)]
    #[Assert\NotBlank]
    #[Groups(['petition:write'])]
    public ?string $postalCode = null;

    #[AssertPhoneNumber]
    #[Groups(['petition:write'])]
    public ?PhoneNumber $phone = null;

    #[Assert\IsTrue(message: 'Merci de lire et d\'accepter les conditions générales d\'utilisation avant de poursuivre.')]
    #[Groups(['petition:write'])]
    public ?bool $cguAccepted = false;

    #[Groups(['petition:write'])]
    public ?bool $newsletter = false;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['petition:write'])]
    public ?string $petitionName = null;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['petition:write'])]
    public ?string $petitionSlug = null;

    #[Assert\Length(max: 255)]
    #[Groups(['petition:write'])]
    public ?string $utmSource = null;

    #[Assert\Length(max: 255)]
    #[Groups(['petition:write'])]
    public ?string $utmCampaign = null;
}
