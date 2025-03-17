<?php

namespace App\Adhesion\Request;

use App\Address\Address;
use App\Donation\Request\DonationRequestInterface;
use App\Entity\Adherent;
use App\Subscription\SubscriptionTypeEnum;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\StrictEmail;
use App\ValueObject\Genders;
use Symfony\Component\Validator\Constraints as Assert;

#[MaxFiscalYearDonation(path: 'amount', groups: ['adhesion:amount'])]
class MembershipRequest implements DonationRequestInterface
{
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

    #[Assert\Valid]
    public ?Address $address = null;

    #[Assert\Country(message: 'common.nationality.invalid')]
    #[Assert\NotBlank]
    public ?string $nationality = null;

    public ?bool $exclusiveMembership = null;

    #[Assert\AtLeastOneOf([
        new Assert\Expression('this.exclusiveMembership'),
        new Assert\Choice([1, 2, 3]),
    ], message: 'Ce champ est requis.')]
    public ?int $partyMembership = null;

    #[Assert\IsTrue(groups: ['adhesion'])]
    public bool $isPhysicalPerson = false;

    #[Assert\GreaterThanOrEqual(value: 10, message: "Le montant de la cotisation n'est pas valide", groups: ['adhesion:amount'])]
    #[Assert\NotBlank(message: "Afin d'adhérer à Renaissance vous devez payer la valeur minimale de notre adhésion.", groups: ['adhesion:amount'])]
    public ?int $amount = null;

    public ?bool $allowNotifications = null;

    public ?string $referrer = null;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;

    public static function createFromAdherent(Adherent $adherent): self
    {
        $request = new self();

        $request->civility = $adherent->getGender();
        $request->firstName = $adherent->getFirstName();
        $request->lastName = $adherent->getLastName();
        $request->email = $adherent->getEmailAddress();
        $request->nationality = $adherent->getNationality();
        $request->address = Address::createFromAddress($adherent->getPostAddress());
        $request->exclusiveMembership = $adherent->isExclusiveMembership();
        $request->partyMembership = $adherent->isTerritoireProgresMembership() ? 1 : ($adherent->isAgirMembership() ? 2 : ($adherent->isOtherPartyMembership() ? 3 : null));
        $request->allowNotifications = $adherent->hasSubscriptionType(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL);

        return $request;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getEmailAddress(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = strtolower($email);
    }
}
