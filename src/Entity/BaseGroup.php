<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Report\ReportableInterface;
use AppBundle\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * An abstract group class.
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseGroup implements GeoPointInterface, CoordinatorAreaInterface, ReportableInterface
{
    public const APPROVED = 'APPROVED';
    public const PENDING = 'PENDING';
    public const REFUSED = 'REFUSED';

    use CoordinatorAreaTrait;
    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    /**
     * The group current status.
     *
     * @ORM\Column(length=20)
     *
     * @JMS\Groups({"public", "committee_read", "citizen_project_read"})
     */
    protected $status;

    /**
     * The timestamp when an administrator approved this group.
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @JMS\Groups({"citizen_project_read"})
     * @JMS\SerializedName("approvedAt")
     */
    protected $approvedAt;

    /**
     * The timestamp when an administrator refused this group.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $refusedAt;

    /**
     * The adherent UUID who created this group.
     *
     * @ORM\Column(type="uuid", nullable=true)
     */
    protected $createdBy;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    protected $phone;

    /**
     * The cached number of members (followers and hosts/administrators).
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Algolia\Attribute
     *
     * @JMS\Groups({"public", "committee_read", "citizen_project_read"})
     * @JMS\SerializedName("membersCount")
     */
    protected $membersCount;

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $creator,
        string $name,
        string $slug = null,
        PhoneNumber $phone = null,
        string $status = self::PENDING,
        string $approvedAt = null,
        string $createdAt = 'now',
        int $membersCount = 0
    ) {
        if ($approvedAt) {
            $approvedAt = new \DateTimeImmutable($approvedAt);
        }

        if ($createdAt) {
            $createdAt = new \DateTimeImmutable($createdAt);
        }

        $this->uuid = $uuid;
        $this->createdBy = $creator;
        $this->setName($name);
        $this->slug = $slug;
        $this->phone = $phone;
        $this->status = $status;
        $this->membersCount = $membersCount;
        $this->approvedAt = $approvedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
    }

    public function __toString()
    {
        return $this->name ?: '';
    }

    public static function createUuid(string $name): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, static::canonicalize($name));
    }

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function isWaitingForApproval(): bool
    {
        return self::PENDING === $this->status && !$this->approvedAt;
    }

    /**
     * @Algolia\IndexIf
     */
    public function isApproved(): bool
    {
        return self::APPROVED === $this->status && $this->approvedAt;
    }

    public function isPending(): bool
    {
        return self::PENDING === $this->status;
    }

    public function isRefused(): bool
    {
        return self::REFUSED === $this->status;
    }

    public function getMembersCount(): int
    {
        return $this->membersCount;
    }

    public function incrementMembersCount(int $increment = 1): void
    {
        $this->membersCount += $increment;
    }

    public function decrementMembersCount(int $increment = 1): void
    {
        $this->membersCount -= $increment;
    }

    /**
     * Marks this group as refused/rejected.
     */
    public function refused(string $timestamp = 'now')
    {
        $this->status = self::REFUSED;
        $this->refusedAt = new \DateTime($timestamp);
        $this->approvedAt = null;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy ? $this->createdBy->toString() : null;
    }

    public function isCreatedBy(UuidInterface $uuid): bool
    {
        return $this->createdBy && $this->createdBy->equals($uuid);
    }

    /**
     * Returns the approval date and time.
     */
    public function getApprovedAt(): ?\DateTime
    {
        return $this->approvedAt;
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("uuid"),
     * @JMS\Groups({"public", "committee_read", "citizen_project_read"})
     */
    public function getUuidAsString(): string
    {
        return $this->getUuid()->toString();
    }
}
