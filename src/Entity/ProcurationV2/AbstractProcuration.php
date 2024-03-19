<?php

namespace App\Entity\ProcurationV2;

use App\Entity\Adherent;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractProcuration
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityPostAddressTrait;
    use EntityAdministratorBlameableTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="procuration.email.not_blank")
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    public string $email;

    /**
     * @ORM\Column(length=6)
     *
     * @Assert\NotBlank(message="common.gender.invalid_choice")
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice"
     * )
     */
    public string $gender;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="procuration.first_names.not_blank")
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="procuration.first_names.min_length",
     *     maxMessage="procuration.first_names.max_length"
     * )
     */
    public string $firstNames;

    /**
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank(message="procuration.last_name.not_blank")
     * @Assert\Length(
     *     min=1,
     *     max=100,
     *     minMessage="procuration.last_name.min_length",
     *     maxMessage="procuration.last_name.max_length"
     * )
     */
    public string $lastName;

    /**
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank(message="procuration.birthdate.not_blank")
     * @Assert\Range(
     *     min="-120 years",
     *     max="-17 years",
     *     minMessage="procuration.birthdate.maximum_required_age",
     *     maxMessage="procuration.birthdate.minimum_required_age"
     * )
     */
    public \DateTimeInterface $birthdate;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(message="common.phone_number.invalid")
     */
    public ?PhoneNumber $phone;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $distantVotePlace;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank(message="procuration.vote_zone.not_blank")
     */
    public Zone $voteZone;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinColumn(nullable=true)
     */
    public ?Zone $votePlace;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $customVotePlace;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    public ?string $clientIp;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Adherent $adherent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcurationV2\Round")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank(message="procuration.round.not_blank")
     */
    public Round $round;

    public function __construct(
        Round $round,
        string $email,
        string $gender,
        string $firstNames,
        string $lastName,
        \DateTimeInterface $birthdate,
        ?PhoneNumber $phone,
        PostAddress $postAddress,
        bool $distantVotePlace,
        Zone $voteZone,
        ?Zone $votePlace = null,
        ?string $customVotePlace = null,
        ?Adherent $adherent = null,
        ?string $clientIp = null,
        ?\DateTimeInterface $createdAt = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->round = $round;
        $this->email = $email;
        $this->gender = $gender;
        $this->firstNames = $firstNames;
        $this->lastName = $lastName;
        $this->birthdate = $birthdate;
        $this->phone = $phone;
        $this->postAddress = $postAddress;
        $this->distantVotePlace = $distantVotePlace;
        $this->voteZone = $voteZone;
        $this->votePlace = $votePlace;
        $this->customVotePlace = $customVotePlace;
        $this->adherent = $adherent;
        $this->clientIp = $clientIp;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s] %s %s (%s)',
            $this->id,
            $this->firstNames,
            $this->lastName,
            $this->email
        );
    }

    public function getPostAddress(): PostAddress
    {
        return $this->postAddress;
    }
}
