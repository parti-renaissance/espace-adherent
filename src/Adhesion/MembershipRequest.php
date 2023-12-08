<?php

namespace App\Adhesion;

use App\Address\Address;
use App\Validator\StrictEmail;
use Symfony\Component\Validator\Constraints as Assert;

class MembershipRequest
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
     *     @Assert\Choice(choices=1, 2, 3),
     * }, message="Ce champ est requis.", includeInternalMessages=false)
     */
    public ?int $partyMembership = null;

    /**
     * @Assert\IsTrue
     */
    public bool $isPhysicalPerson = false;

    public bool $allowNotifications = false;

    public ?string $utmSource = null;
    public ?string $utmCampaign = null;
}
