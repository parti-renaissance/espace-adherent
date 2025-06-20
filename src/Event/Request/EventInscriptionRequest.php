<?php

namespace App\Event\Request;

use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\NationalEventTransportMode;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\StrictEmail;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertRecaptcha]
#[NationalEventTransportMode(groups: ['national_event_campus'])]
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
    #[Assert\Range(maxMessage: 'Vous devez être âgé d\'au moins 15 ans', max: '-15 years', groups: ['national_event_campus'])]
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

    public ?string $visitDay = null;
    public ?string $transport = null;
    public bool $withDiscount = false;

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

    public function __construct(
        public readonly int $eventId,
        public readonly string $sessionId,
        public readonly string $clientIp,
        public readonly ?array $transportConfiguration = null,
    ) {
    }

    public static function fromInscription(EventInscription $inscription): self
    {
        $request = new self($inscription->event->getId(), $inscription->sessionId ?? '', $inscription->clientIp ?? '');
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
        $request->qualities = $inscription->qualities;
        $request->accessibility = $inscription->accessibility;
        $request->allowNotifications = $inscription->joinNewsletter;
        $request->isJAM = $inscription->isJAM;

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
}
