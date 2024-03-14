<?php

namespace App\Entity\Procuration;

use App\Entity\Adherent;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;

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
     */
    public string $email;

    /**
     * @ORM\Column(length=6)
     */
    public string $gender;

    /**
     * @ORM\Column
     */
    public string $firstNames;

    /**
     * @ORM\Column(length=100)
     */
    public string $lastName;

    /**
     * @ORM\Column(type="date")
     */
    public \DateTimeInterface $birthdate;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    public ?PhoneNumber $phone;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $distantVotePlace;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinColumn(nullable=false)
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Procuration\Round")
     * @ORM\JoinColumn(nullable=false)
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
}
