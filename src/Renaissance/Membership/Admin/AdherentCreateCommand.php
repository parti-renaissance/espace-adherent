<?php

namespace App\Renaissance\Membership\Admin;

use App\Address\Address;
use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Validator\BannedAdherent;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

class AdherentCreateCommand implements MembershipInterface
{
    #[Assert\Choice(choices: Genders::CIVILITY_CHOICES, message: 'common.gender.invalid_choice', groups: ['admin_adherent_renaissance_create'])]
    #[Assert\NotBlank(message: 'common.gender.not_blank', groups: ['admin_adherent_renaissance_create'])]
    public ?string $gender = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 2, max: 50, minMessage: 'admin.common.first_name.min_length', maxMessage: 'admin.common.first_name.max_length'),
    ], groups: ['admin_adherent_renaissance_create'])]
    public ?string $firstName = null;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 1, max: 50, minMessage: 'admin.common.last_name.min_length', maxMessage: 'admin.common.last_name.max_length'),
    ], groups: ['admin_adherent_renaissance_create'])]
    public ?string $lastName = null;

    #[Assert\Country(message: 'common.nationality.invalid', groups: ['admin_adherent_renaissance_create'])]
    #[Assert\NotBlank(message: 'adherent_profile.nationality.not_blank', groups: ['admin_adherent_renaissance_create'])]
    public ?string $nationality = AddressInterface::FRANCE;

    #[Assert\Valid(groups: ['admin_adherent_renaissance_create'])]
    public Address $address;

    #[Assert\Email(message: 'common.email.invalid', groups: ['admin_adherent_renaissance_create', 'admin_adherent_renaissance_verify_email'])]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length', groups: ['admin_adherent_renaissance_create', 'admin_adherent_renaissance_verify_email'])]
    #[Assert\NotBlank(message: 'common.email.not_blank', groups: ['admin_adherent_renaissance_create', 'admin_adherent_renaissance_verify_email'])]
    #[BannedAdherent]
    public ?string $email = null;

    #[AssertPhoneNumber(groups: ['admin_adherent_renaissance_create'])]
    public ?PhoneNumber $phone = null;

    #[Assert\NotBlank(message: 'admin.common.birthdate.not_blank', groups: ['admin_adherent_renaissance_create'])]
    #[Assert\Range(
        notInRangeMessage: 'common.birthdate.not_in_range',
        min: '-120 years',
        max: '-15 years',
        groups: ['admin_adherent_renaissance_create']
    )]
    public ?\DateTimeInterface $birthdate = null;

    #[Assert\Choice(choices: MembershipTypeEnum::CHOICES, message: 'admin.adherent.renaissance.membership_type.invalid_choice', groups: ['admin_adherent_renaissance_create'])]
    #[Assert\NotBlank(message: 'admin.adherent.renaissance.membership_type.not_blank', groups: ['admin_adherent_renaissance_create'])]
    public ?string $partyMembership = MembershipTypeEnum::EXCLUSIVE;

    #[Assert\Choice(choices: CotisationTypeChoiceEnum::CHOICES, message: 'admin.membership.cotisation_type_choice.invalid_choice', groups: ['admin_adherent_renaissance_create'])]
    #[Assert\NotBlank(message: 'admin.membership.cotisation_amount_choice.not_blank', groups: ['admin_adherent_renaissance_create'])]
    public ?string $cotisationTypeChoice = CotisationTypeChoiceEnum::TYPE_CHECK;

    #[Assert\Choice(choices: CotisationAmountChoiceEnum::CHOICES, message: 'admin.membership.cotisation_amount_choice.invalid_choice', groups: ['admin_adherent_renaissance_create'])]
    #[Assert\NotBlank(message: 'admin.membership.cotisation_amount_choice.not_blank', groups: ['admin_adherent_renaissance_create'])]
    public ?string $cotisationAmountChoice = CotisationAmountChoiceEnum::AMOUNT_30;

    #[Assert\Expression("this.cotisationAmountChoice != 'amount_other' or this.cotisationCustomAmount > 0", groups: ['admin_adherent_renaissance_create'], message: 'Le montant de la cotisation doit être positif')]
    #[Assert\Range(minMessage: 'donation.amount.greater_than_0', min: '0.01', groups: ['admin_adherent_renaissance_create'])]
    #[Assert\Range(maxMessage: 'donation.amount.less_than_7500', max: 7500, groups: ['admin_adherent_renaissance_create'])]
    public ?float $cotisationCustomAmount = null;

    #[Assert\LessThanOrEqual('today')]
    public \DateTime $cotisationDate;

    private bool $isCertified = false;

    public function __construct(public ?string $source = null)
    {
        $this->cotisationDate = new \DateTime();
    }

    public function isCotisationTypeTPE(): bool
    {
        return CotisationTypeChoiceEnum::TYPE_TPE === $this->cotisationTypeChoice;
    }

    public function getCotisationAmount(): int
    {
        return match ($this->cotisationAmountChoice) {
            CotisationAmountChoiceEnum::AMOUNT_10 => 10,
            CotisationAmountChoiceEnum::AMOUNT_30 => 30,
            CotisationAmountChoiceEnum::AMOUNT_OTHER => $this->cotisationCustomAmount,
        };
    }

    public function getEmailAddress(): ?string
    {
        return $this->email;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function isCertified(): bool
    {
        return $this->isCertified;
    }

    public function updateFromAdherent(Adherent $adherent): void
    {
        $this->isCertified = $adherent->isCertified();
        $this->gender = $adherent->getGender();
        $this->firstName = $adherent->getFirstName();
        $this->lastName = $adherent->getLastName();
        $this->address = Address::createFromAddress($adherent->getPostAddress());
        $this->phone = $adherent->getPhone();
        $this->birthdate = $adherent->getBirthdate();
    }
}
