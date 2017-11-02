<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Exception\GroupAlreadyApprovedException;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * This entity represents a group.
 *
 * @ORM\Table(
 *   name="groups",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="group_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="group_canonical_name_unique", columns="canonical_name"),
 *     @ORM\UniqueConstraint(name="group_slug_unique", columns="slug")
 *   },
 *   indexes={
 *     @ORM\Index(name="group_status_idx", columns="status")
 *   }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Group extends BaseGroup
{
    public function __construct(
        UuidInterface $uuid,
        UuidInterface $creator,
        string $name,
        string $description,
        PostAddress $address = null,
        PhoneNumber $phone = null,
        string $slug = null,
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
        $this->description = $description;
        $this->postAddress = $address;
        $this->phone = $phone;
        $this->status = $status;
        $this->membersCounts = $membersCount;
        $this->approvedAt = $approvedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
    }

    public static function createSimple(UuidInterface $uuid, string $creatorUuid, string $name, string $description, PhoneNumber $phone, PostAddress $address = null, string $createdAt = 'now'): self
    {
        $group = new self(
            $uuid,
            Uuid::fromString($creatorUuid),
            $name,
            $description,
            $address,
            $phone
        );
        $group->createdAt = new \DateTime($createdAt);

        return $group;
    }

    public static function createForAdherent(Adherent $adherent, string $name, string $description, PhoneNumber $phone, PostAddress $address = null, string $createdAt = 'now'): self
    {
        $group = new self(
            self::createUuid($name),
            clone $adherent->getUuid(),
            $name,
            $description,
            $address,
            $phone
        );
        $group->createdAt = new \DateTime($createdAt);

        return $group;
    }

    /**
     * Marks this committee as approved.
     *
     * @param string $timestamp
     */
    public function approved(string $timestamp = 'now'): void
    {
        if ($this->isApproved()) {
            throw new GroupAlreadyApprovedException($this->uuid);
        }

        $this->status = self::APPROVED;
        $this->approvedAt = new \DateTime($timestamp);
        $this->refusedAt = null;
    }
}
