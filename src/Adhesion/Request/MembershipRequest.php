<?php

namespace App\Adhesion\Request;

use App\Address\Address;
use App\Donation\Request\DonationRequestInterface;
use App\Entity\Adherent;
use App\Subscription\SubscriptionTypeEnum;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\StrictEmail;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MaxFiscalYearDonation(groups={"adhesion:amount"}, path="amount")
 */
class MembershipRequest implements DonationRequestInterface
{
    /**
     * @Assert\NotBlank
     * @StrictEmail(dnsCheck=false)
     */
    public ?string $email = null;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\ValueObject\Genders", "all"}, message="common.invalid_choice")
     */
    public ?string $civility = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     allowEmptyString=true,
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length"
     * )
     */
    public ?string $firstName = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     allowEmptyString=true,
     *     min=1,
     *     max=50,
     *     minMessage="common.last_name.min_length",
     *     maxMessage="common.last_name.max_length"
     * )
     */
    public ?string $lastName = null;

    /**
     * @Assert\Valid
     */
    public ?Address $address = null;

    /**
     * @Assert\NotBlank
     * @Assert\Country(message="common.nationality.invalid")
     */
    public ?string $nationality = null;

    /**
     * @Assert\Type("bool")
     */
    public ?bool $exclusiveMembership = null;

    /**
     * @Assert\AtLeastOneOf({
     *     @Assert\Expression("this.exclusiveMembership"),
     *     @Assert\Choice({1, 2, 3}),
     * }, message="Ce champ est requis.", includeInternalMessages=false)
     */
    public ?int $partyMembership = null;

    /**
     * @Assert\IsTrue
     */
    public bool $isPhysicalPerson = false;

    /**
     * @Assert\NotBlank(groups={"adhesion:amount"}, message="Afin d'adhérer à Renaissance vous devez payer la valeur minimal de notre adhésion.")
     * @Assert\GreaterThanOrEqual(value=10, message="Le montant de la cotisation n'est pas valide", groups={"adhesion:amount"})
     */
    public ?int $amount = null;

    public bool $allowNotifications = false;

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
}
