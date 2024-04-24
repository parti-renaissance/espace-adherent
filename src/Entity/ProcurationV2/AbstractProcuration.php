<?php

namespace App\Entity\ProcurationV2;

use App\Address\AddressInterface;
use App\Adherent\Tag\TranslatedTagInterface;
use App\Entity\Adherent;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Entity\ZoneableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractProcuration implements ZoneableEntity, TranslatedTagInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityPostAddressTrait;
    use EntityAdministratorBlameableTrait;

    /**
     * @ORM\Column
     *
     * @Assert\Email(message="common.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     *
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_matched_proxy",
     * })
     */
    public string $email;

    /**
     * @ORM\Column(length=6)
     *
     * @Assert\Choice(
     *     callback={"App\ValueObject\Genders", "all"},
     *     message="common.gender.invalid_choice"
     * )
     *
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_proxy_list_request",
     *     "procuration_request_list_proxy",
     * })
     */
    public string $gender;

    /**
     * @ORM\Column
     *
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="procuration.first_names.min_length",
     *     maxMessage="procuration.first_names.max_length"
     * )
     *
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_proxy_list_request",
     *     "procuration_request_list_proxy",
     *     "procuration_matched_proxy",
     * })
     */
    public string $firstNames;

    /**
     * @ORM\Column(length=100)
     *
     * @Assert\Length(
     *     min=1,
     *     max=100,
     *     minMessage="procuration.last_name.min_length",
     *     maxMessage="procuration.last_name.max_length"
     * )
     *
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_proxy_list_request",
     *     "procuration_request_list_proxy",
     *     "procuration_matched_proxy",
     * })
     */
    public string $lastName;

    /**
     * @ORM\Column(type="date")
     *
     * @Assert\Range(
     *     min="-120 years",
     *     max="-17 years",
     *     minMessage="procuration.birthdate.maximum_required_age",
     *     maxMessage="procuration.birthdate.minimum_required_age"
     * )
     *
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_matched_proxy",
     * })
     */
    public \DateTimeInterface $birthdate;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     *
     * @AssertPhoneNumber(message="common.phone_number.invalid")
     *
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_matched_proxy",
     * })
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
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_matched_proxy",
     * })
     */
    public Zone $voteZone;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Geo\Zone")
     * @ORM\JoinColumn(nullable=true)
     */
    public ?Zone $votePlace;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(
     *     max=255,
     *     maxMessage="procuration.custom_vote_place.max_length"
     * )
     */
    public ?string $customVotePlace;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $joinNewsletter;

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
        bool $joinNewsletter = false,
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
        $this->joinNewsletter = $joinNewsletter;
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

    /**
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_matched_proxy",
     * })
     */
    public function getAge(): ?int
    {
        return $this->birthdate?->diff(new \DateTime())->y;
    }

    /**
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_matched_proxy",
     *     "procuration_proxy_list_request",
     *     "procuration_request_list_proxy",
     * })
     * @SerializedName("id")
     */
    public function getPublicId(): string
    {
        return substr($this->uuid->toString(), 0, 6 - \strlen($this->id)).$this->id;
    }

    public function getZones(): Collection
    {
        return new ArrayCollection([$this->voteZone]);
    }

    public function clearZones(): void
    {
    }

    public function removeZone(Zone $zone): void
    {
    }

    public function addZone(Zone $Zone): void
    {
    }

    public static function getZonesPropertyName(): string
    {
        return 'voteZone';
    }

    /**
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_matched_proxy",
     * })
     */
    public function getVotePlaceName(): ?string
    {
        return $this->customVotePlace ?? $this->votePlace?->getName();
    }

    /**
     * @Groups({
     *     "procuration_request_read",
     *     "procuration_request_list",
     *     "procuration_proxy_list",
     *     "procuration_matched_proxy",
     * })
     * @SerializedName("tags")
     */
    public function getAdherentTags(): ?array
    {
        return $this->adherent?->tags;
    }

    public function isFDE(): bool
    {
        return $this->voteZone->isCountry() && AddressInterface::FRANCE !== $this->voteZone->getCode();
    }
}
