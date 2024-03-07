<?php

namespace App\Procuration\V2\Command;

use App\Address\Address;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Procuration\Round;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractCommand
{
    /**
     * @Assert\NotBlank(message="common.gender.invalid_choice")
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice"
     * )
     */
    public ?string $gender = null;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="procuration.first_names.min_length",
     *     maxMessage="procuration.first_names.max_length"
     * )
     */
    public ?string $firstNames = null;

    /**
     * @Assert\NotBlank(groups={"Default", "fill_personal_info"})
     * @Assert\Length(
     *     min=1,
     *     max=100,
     *     minMessage="procuration.last_name.min_length",
     *     maxMessage="procuration.last_name.max_length"
     * )
     */
    public ?string $lastName = null;

    /**
     * @Assert\NotBlank
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    public ?string $email = null;

    /**
     * @Assert\NotBlank(message="procuration.birthdate.not_blank")
     * @Assert\Range(
     *     min="-120 years",
     *     max="-17 years",
     *     minMessage="procuration.birthdate.maximum_required_age",
     *     maxMessage="procuration.birthdate.minimum_required_age"
     * )
     */
    public ?\DateTimeInterface $birthdate = null;

    /**
     * @AssertPhoneNumber(message="common.phone_number.invalid")
     */
    public ?PhoneNumber $phone = null;

    /**
     * @Assert\NotBlank
     * @Assert\Valid
     */
    public ?Address $address = null;

    public bool $distantVotePlace = false;

    /**
     * @Assert\NotBlank
     */
    public ?Zone $voteZone = null;

    /**
     * @Assert\NotBlank
     */
    public ?Zone $votePlace = null;

    public ?string $clientIp = null;

    public ?Adherent $adherent = null;

    /**
     * @Assert\NotBlank
     */
    public ?Round $round = null;
}
