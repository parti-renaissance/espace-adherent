<?php

declare(strict_types=1);

namespace App\NationalEvent\DTO;

use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\NationalEventPackage;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\RoommateIdentifier;
use App\Validator\StrictEmail;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertRecaptcha(groups: ['inscription:creation'])]
#[NationalEventPackage(groups: ['inscription:package'])]
class InscriptionRequest implements RecaptchaChallengeInterface
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
    #[Assert\Range(maxMessage: 'Vous devez être âgé d\'au moins 15 ans', max: '-15 years', groups: ['inscription:user_data'])]
    public ?\DateTime $birthdate = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(max: 255),
    ], groups: ['inscription:user_data:default', 'inscription:user_data:campus'])]
    public ?string $birthPlace = null;

    #[AssertPhoneNumber(message: 'common.phone_number.invalid')]
    #[Assert\NotBlank(groups: ['inscription:user_data:jem'])]
    public ?PhoneNumber $phone = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 4, max: 10),
    ])]
    public ?string $postalCode = null;

    public ?string $visitDay = null;

    public ?string $transport = null;

    public ?string $packagePlan = null;

    #[Assert\NotBlank(groups: ['inscription:user_data:jem'])]
    public ?string $emergencyContactName = null;

    #[Assert\NotBlank(groups: ['inscription:user_data:jem'])]
    public ?PhoneNumber $emergencyContactPhone = null;

    #[Assert\Expression('this.transport != "avec_transport" or this.packageCity', message: 'Veillez sélectionner la ville de départ.', groups: ['inscription:package:jem'])]
    public ?string $packageCity = null;

    #[Assert\Expression('this.transport != "avec_transport" or this.packageDepartureTime', message: 'Veillez sélectionner votre préférence de départ', groups: ['inscription:package:jem'])]
    public ?string $packageDepartureTime = null;

    public ?string $packageDonation = null;

    public ?string $accommodation = null;
    public bool $withDiscount = false;

    #[Assert\Length(min: 6, max: 7)]
    #[RoommateIdentifier]
    public ?string $roommateIdentifier = null;

    public bool $transportNeeds = false;
    public bool $volunteer = false;

    public bool $allowNotifications = false;

    public bool $isJAM = false;
    public bool $withChildren = false;

    #[Assert\Expression('!this.withChildren or this.isResponsibilityWaived', message: 'Veillez cocher cette case.')]
    public bool $isResponsibilityWaived = false;

    #[Assert\Expression('!this.withChildren or this.children', message: 'Ce champ est requis.')]
    #[Assert\Length(max: 255)]
    public ?string $children = null;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;

    public ?string $referrerCode = null;

    public array $qualities = [];

    #[Assert\Length(max: 255)]
    public ?string $accessibility = null;

    private array $packageValues = [];

    public function __construct(
        public readonly int $eventId,
        public readonly string $sessionId,
        public readonly string $clientIp,
        public readonly ?array $packageConfig = null,
        public readonly ?int $inscriptionId = null,
    ) {
    }

    public static function fromInscription(EventInscription $inscription): self
    {
        $request = new self(
            $inscription->event->getId(),
            $inscription->sessionId ?? '',
            $inscription->clientIp ?? '',
            $inscription->event->packageConfig,
            $inscription->getId(),
        );
        $request->email = $inscription->addressEmail;
        $request->civility = $inscription->gender;
        $request->firstName = $inscription->firstName;
        $request->lastName = $inscription->lastName;
        $request->birthdate = $inscription->birthdate;
        $request->birthPlace = $inscription->birthPlace;
        $request->phone = $inscription->phone;
        $request->postalCode = $inscription->postalCode;
        $request->transportNeeds = $inscription->transportNeeds;
        $request->volunteer = $inscription->volunteer;
        $request->withChildren = null !== $inscription->children;
        $request->isResponsibilityWaived = $inscription->isResponsibilityWaived;
        $request->children = $inscription->children;
        $request->qualities = $inscription->qualities ?? [];
        $request->accessibility = $inscription->accessibility;
        $request->allowNotifications = $inscription->joinNewsletter;
        $request->isJAM = $inscription->isJAM;
        $request->visitDay = $inscription->visitDay;
        $request->transport = $inscription->transport;
        $request->accommodation = $inscription->accommodation;
        $request->withDiscount = $inscription->withDiscount ?? false;
        $request->packagePlan = $inscription->packagePlan;
        $request->packageCity = $inscription->packageCity;
        $request->packageDepartureTime = $inscription->packageDepartureTime;
        $request->packageDonation = $inscription->packageDonation;
        $request->emergencyContactName = $inscription->emergencyContactName;
        $request->emergencyContactPhone = $inscription->emergencyContactPhone;

        $request->packageValues = $inscription->packageValues ?? [];

        return $request;
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

    public function getPackageValues(): array
    {
        return $this->packageValues;
    }

    public function setPackageValues(array $values): void
    {
        $this->packageValues = array_filter($values, static fn ($v) => null !== $v && '' !== $v);
    }
}
