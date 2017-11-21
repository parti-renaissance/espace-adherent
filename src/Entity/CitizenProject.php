<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Exception\CitizenProjectAlreadyApprovedException;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * This entity represents a citizen project.
 *
 * @ORM\Table(
 *   name="citizen_projects",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="citizen_project_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="citizen_project_canonical_name_unique", columns="canonical_name"),
 *     @ORM\UniqueConstraint(name="citizen_project_slug_unique", columns="slug")
 *   },
 *   indexes={
 *     @ORM\Index(name="citizen_project_status_idx", columns="status")
 *   }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenProjectRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProject extends BaseGroup
{
    use EntityNullablePostAddressTrait;

    /**
     * A cached list of the administrators (for admin).
     */
    public $administrators = [];

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $creator,
        string $name,
        string $description,
        NullablePostAddress $address = null,
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

    public function getPostAddress(): NullablePostAddress
    {
        return $this->postAddress;
    }

    public function getLatitude()
    {
        return $this->postAddress ? $this->postAddress->getLatitude() : null;
    }

    public function getLongitude()
    {
        return $this->postAddress ? $this->postAddress->getLongitude() : null;
    }

    public function getGeocodableAddress(): string
    {
        return $this->postAddress ? $this->postAddress->getGeocodableAddress() : '';
    }

    public static function createSimple(UuidInterface $uuid, string $creatorUuid, string $name, string $description, NullablePostAddress $address = null, PhoneNumber $phone = null, string $createdAt = 'now'): self
    {
        $citizenProject = new self(
            $uuid,
            Uuid::fromString($creatorUuid),
            $name,
            $description,
            $address,
            $phone
        );
        $citizenProject->createdAt = new \DateTime($createdAt);

        return $citizenProject;
    }

    public static function createForAdherent(Adherent $adherent, string $name, string $description, NullablePostAddress $address = null, PhoneNumber $phone = null, string $createdAt = 'now'): self
    {
        $citizenProject = new self(
            self::createUuid($name),
            clone $adherent->getUuid(),
            $name,
            $description,
            $address,
            $phone
        );
        $citizenProject->createdAt = new \DateTime($createdAt);

        return $citizenProject;
    }

    /**
     * Marks this citizen project as approved.
     *
     * @param string $timestamp
     */
    public function approved(string $timestamp = 'now'): void
    {
        if ($this->isApproved()) {
            throw new CitizenProjectAlreadyApprovedException($this->uuid);
        }

        $this->status = self::APPROVED;
        $this->approvedAt = new \DateTime($timestamp);
        $this->refusedAt = null;
    }

    public function update(string $name, string $description, NullablePostAddress $address, PhoneNumber $phone): void
    {
        $this->setName($name);
        $this->description = $description;

        if (null === $this->postAddress || !$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }

        if (null === $this->phone || !$this->phone->equals($phone)) {
            $this->phone = $phone;
        }
    }
}
