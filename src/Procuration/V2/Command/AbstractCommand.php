<?php

namespace App\Procuration\V2\Command;

use App\Address\Address;
use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\ArrayCollection;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Assert\Expression("!this.voteZone || !this.voteZone.isInFrance() || this.votePlace || this.customVotePlace", message="procuration.vote_place.not_blank")
 */
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
     * @Assert\NotBlank(message="procuration.first_names.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="procuration.first_names.min_length",
     *     maxMessage="procuration.first_names.max_length"
     * )
     */
    public ?string $firstNames = null;

    /**
     * @Assert\NotBlank(message="procuration.last_name.not_blank")
     * @Assert\Length(
     *     min=1,
     *     max=100,
     *     minMessage="procuration.last_name.min_length",
     *     maxMessage="procuration.last_name.max_length"
     * )
     */
    public ?string $lastName = null;

    /**
     * @Assert\NotBlank(message="procuration.email.not_blank")
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
     * @Assert\NotBlank(message="common.phone_number.required")
     * @AssertPhoneNumber(message="common.phone_number.invalid")
     */
    public ?PhoneNumber $phone = null;

    /**
     * @Assert\NotBlank(message="procuration.address.not_blank")
     * @Assert\Valid
     */
    public ?Address $address = null;

    public bool $distantVotePlace = false;

    /**
     * @Assert\NotBlank(message="procuration.vote_zone.not_blank")
     */
    public ?Zone $voteZone = null;

    public ?Zone $votePlace = null;

    /**
     * @Assert\Length(max=255)
     */
    public ?string $customVotePlace = null;

    public bool $joinNewsletter = false;

    public ?string $clientIp = null;

    public ?Adherent $adherent = null;

    /**
     * @Assert\Count(min=1, minMessage="procuration.rounds.min")
     */
    public ArrayCollection $rounds;

    public function __construct()
    {
        $this->rounds = new ArrayCollection();
    }

    public function isFDE(): bool
    {
        return $this->voteZone
            && $this->voteZone->isCountry()
            && AddressInterface::FRANCE !== $this->voteZone->getCode();
    }

    public function setEmail(?string $email): void
    {
        $this->email = mb_strtolower($email);
    }
}
