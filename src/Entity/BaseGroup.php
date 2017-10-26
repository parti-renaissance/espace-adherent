<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * An abstract group class.
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseGroup implements GeoPointInterface
{
    const APPROVED = 'APPROVED';
    const PENDING = 'PENDING';
    const REFUSED = 'REFUSED';

    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityTimestampableTrait;
    use EntityPostAddressTrait;

    /**
     * The group name.
     *
     * @ORM\Column
     *
     * @Algolia\Attribute
     */
    protected $name;

    /**
     * The group name.
     *
     * @ORM\Column
     *
     * @Algolia\Attribute
     */
    protected $canonicalName;

    /**
     * The group slug.
     *
     * @ORM\Column
     *
     * @Gedmo\Slug(fields={"canonicalName"})
     *
     * @Algolia\Attribute
     */
    protected $slug;

    /**
     * The group description.
     *
     * @ORM\Column(type="text")
     *
     * @Algolia\Attribute
     */
    protected $description;

    /**
     * The group current status.
     *
     * @ORM\Column(length=20)
     */
    protected $status;

    /**
     * The timestamp when an administrator approved this group.
     *
     * @ORM\Column(type="datetime", nullable=true)
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
     * The cached number of members (followers and hosts).
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Algolia\Attribute
     */
    protected $membersCounts;

    public function __toString()
    {
        return $this->name ?: '';
    }

    public static function createUuid(string $name): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, static::canonicalize($name));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPostAddress(): PostAddress
    {
        return $this->postAddress;
    }

    public function getDescription(): string
    {
        return $this->description;
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
        return $this->membersCounts;
    }

    public function incrementMembersCount(int $increment = 1)
    {
        $this->membersCounts += $increment;
    }

    public function decrementMembersCount(int $increment = 1)
    {
        $this->membersCounts -= $increment;
    }

    /**
     * Marks this group as refused/rejected.
     *
     * @param string $timestamp
     */
    public function refused(string $timestamp = 'now')
    {
        $this->status = self::REFUSED;
        $this->refusedAt = new \DateTime($timestamp);
        $this->approvedAt = null;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        $this->canonicalName = static::canonicalize($name);
    }

    public static function canonicalize(string $name): string
    {
        return mb_strtolower($name);
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy->toString();
    }

    public function isCreatedBy(UuidInterface $uuid): bool
    {
        return $this->createdBy && $this->createdBy->equals($uuid);
    }

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    /**
     * Returns the approval date and time.
     *
     * @return \DateTime|null
     */
    public function getApprovedAt(): ?\DateTime
    {
        return $this->approvedAt;
    }

    public function updateSlug(string $slug)
    {
        $this->slug = $slug;
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    public function update(string $name, string $description, PostAddress $address, PhoneNumber $phone)
    {
        $this->setName($name);
        $this->description = $description;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }

        if (null === $this->phone || !$this->phone->equals($phone)) {
            $this->phone = $phone;
        }
    }
}
