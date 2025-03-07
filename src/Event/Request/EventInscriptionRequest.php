<?php

namespace App\Event\Request;

use App\Entity\Adherent;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\StrictEmail;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertRecaptcha]
class EventInscriptionRequest implements RecaptchaChallengeInterface
{
    use RecaptchaChallengeTrait;

    #[Assert\NotBlank]
    #[StrictEmail(dnsCheck: false)]
    public ?string $email = null;

    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.invalid_choice')]
    #[Assert\NotBlank]
    public ?string $civility = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length'),
    ])]
    public ?string $firstName = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 1, max: 50, minMessage: 'common.last_name.min_length', maxMessage: 'common.last_name.max_length'),
    ])]
    public ?string $lastName = null;

    #[Assert\NotBlank]
    #[Assert\Range(max: '-1 years')]
    public ?\DateTime $birthdate = null;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    public ?string $birthPlace = null;

    #[AssertPhoneNumber(message: 'common.phone_number.invalid')]
    public ?PhoneNumber $phone = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 4, max: 10),
    ])]
    public ?string $postalCode = null;

    public bool $transportNeeds = false;
    public bool $volunteer = false;

    public bool $allowNotifications = false;

    public bool $withChildren = false;

    #[Assert\Expression('!this.withChildren or this.isResponsibilityWaived', message: 'Veillez cocher cette case')]
    public bool $isResponsibilityWaived = false;

    #[Assert\Expression('!this.withChildren or this.children', message: 'Veillez renseigner ce champ')]
    #[Assert\Length(max: 255)]
    public ?string $children = null;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;

    public ?string $referrerCode = null;

    public array $qualities = [];

    #[Assert\Length(max: 255)]
    public ?string $accessibility = null;

    public function __construct(
        public readonly string $sessionId,
        public readonly string $clientIp,
    ) {
    }

    public function updateFromAdherent(Adherent $user): void
    {
        $this->email = $user->getEmailAddress();
        $this->civility = $user->getGender();
        $this->firstName = $user->getFirstName();
        $this->lastName = $user->getLastName();
        $this->birthdate = $user->getBirthdate();
        $this->phone = $user->getPhone();
        $this->postalCode = $user->getPostalCode();
    }
}
