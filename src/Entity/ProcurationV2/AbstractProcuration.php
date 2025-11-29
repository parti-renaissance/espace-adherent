<?php

declare(strict_types=1);

namespace App\Entity\ProcurationV2;

use App\Address\AddressInterface;
use App\Adherent\Tag\TranslatedTagInterface;
use App\Entity\Adherent;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
abstract class AbstractProcuration implements TranslatedTagInterface, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityPostAddressTrait;
    use EntityAdministratorBlameableTrait;
    use OrderedActionsTrait;

    #[Assert\Email(message: 'common.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Groups(['procuration_request_read', 'procuration_matched_proxy', 'procuration_proxy_list'])]
    #[ORM\Column]
    public string $email;

    #[Assert\Choice(callback: [Genders::class, 'all'], message: 'common.gender.invalid_choice')]
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_proxy_list_request', 'procuration_request_list_proxy'])]
    #[ORM\Column(length: 6)]
    public string $gender;

    #[Assert\Length(min: 2, max: 255, minMessage: 'procuration.first_names.min_length', maxMessage: 'procuration.first_names.max_length')]
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_proxy_list_request', 'procuration_request_list_proxy', 'procuration_matched_proxy'])]
    #[ORM\Column]
    public string $firstNames;

    #[Assert\Length(min: 1, max: 100, minMessage: 'procuration.last_name.min_length', maxMessage: 'procuration.last_name.max_length')]
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_proxy_list_request', 'procuration_request_list_proxy', 'procuration_matched_proxy'])]
    #[ORM\Column(length: 100)]
    public string $lastName;

    #[Assert\Range(minMessage: 'procuration.birthdate.maximum_required_age', min: '-120 years')]
    #[Assert\Range(maxMessage: 'procuration.birthdate.minimum_required_age', max: '-17 years')]
    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy'])]
    #[ORM\Column(type: 'date')]
    public \DateTimeInterface $birthdate;

    #[AssertPhoneNumber(message: 'common.phone_number.invalid')]
    #[Groups(['procuration_request_read', 'procuration_matched_proxy', 'procuration_proxy_list'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    public ?PhoneNumber $phone;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $distantVotePlace;

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Zone::class, fetch: 'EXTRA_LAZY')]
    public Zone $voteZone;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    public ?Zone $votePlace;

    #[Assert\Length(max: 255, maxMessage: 'procuration.custom_vote_place.max_length')]
    #[ORM\Column(nullable: true)]
    public ?string $customVotePlace;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $joinNewsletter;

    #[ORM\Column(length: 50, nullable: true)]
    public ?string $clientIp;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent;

    #[ORM\ManyToMany(targetEntity: Round::class)]
    public Collection $rounds;

    #[ORM\Column(nullable: true)]
    public ?string $statusDetail = null;

    /**
     * Ids of main and parents zones built from votePlace zone or from voteZone zone.
     * This field helps to improve matching DB query
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $zoneIds = null;

    public function __construct(
        UuidInterface $uuid,
        array $rounds,
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
        ?\DateTimeInterface $createdAt = null,
    ) {
        $this->uuid = $uuid;
        $this->rounds = new ArrayCollection($rounds);
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
        $this->actions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return \sprintf(
            '[%s] %s %s (%s)',
            $this->id,
            $this->firstNames,
            $this->lastName,
            $this->email
        );
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy'])]
    public function getAge(): ?int
    {
        return $this->birthdate?->diff(new \DateTime())->y;
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy', 'procuration_proxy_list_request', 'procuration_request_list_proxy'])]
    #[SerializedName('id')]
    public function getPublicId(): string
    {
        return substr($this->uuid->toString(), 0, 6 - \strlen((string) $this->id)).$this->id;
    }

    public function getZones(): Collection
    {
        return new ArrayCollection([$this->voteZone]);
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy'])]
    public function getVotePlaceName(): ?string
    {
        return $this->customVotePlace ?? $this->votePlace?->getName();
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy'])]
    #[SerializedName('tags')]
    public function getAdherentTags(): ?array
    {
        return $this->adherent?->tags;
    }

    public function isFDE(): bool
    {
        return $this->voteZone->isCountry() && AddressInterface::FRANCE !== $this->voteZone->getCode();
    }

    /** @return Round[] */
    public function getRounds(): array
    {
        return array_map(fn (AbstractSlot $slot) => $slot->round, $this->getOrderedSlots());
    }

    public function refreshZoneIds(): void
    {
        $this->zoneIds = array_map(
            fn (Zone $zone) => $zone->getId(),
            ($this->votePlace ?? $this->voteZone)->getWithParents()
        );
    }

    #[Groups(['procuration_request_read', 'procuration_request_list', 'procuration_proxy_list', 'procuration_matched_proxy'])]
    public function getDistrict(): ?Zone
    {
        return ($this->votePlace ?? $this->voteZone)->getParentsOfType(Zone::DISTRICT)[0] ?? null;
    }

    abstract public function isExcluded(): bool;

    abstract public function hasFreeSlot(): bool;

    abstract public function hasMatchedSlot(): bool;

    abstract public function hasManualSlot(): bool;

    abstract public function getOrderedSlots(): array;
}
