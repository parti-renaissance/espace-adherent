<?php

namespace AppBundle\Entity\Projection;

use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;

/**
 * This entity is a projection: do not insert, update or delete objects using this class.
 * The table is populated on a regular basis by a background worker to improve performance
 * of SQL queries.
 *
 * @ORM\Table(name="projection_referent_managed_users", indexes={
 *     @ORM\Index(name="projection_referent_managed_users_search", columns={"status", "postal_code", "country"})
 * })
 * @ORM\Entity(readOnly=true, repositoryClass="AppBundle\Repository\Projection\ReferentManagedUserRepository")
 */
class ReferentManagedUser
{
    const STATUS_INSERTING = 0;
    const STATUS_READY = 1;
    const STATUS_EXPIRED = 2;

    const TYPE_ADHERENT = 'adherent';
    const TYPE_NEWSLETTER = 'newsletter';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="bigint", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(length=20)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", options={"unsigned": true})
     */
    private $originalId;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $city;

    /**
     * @var string|null
     *
     * @ORM\Column(length=2, nullable=true)
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50, nullable=true)
     */
    private $lastName;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $age;

    /**
     * @var PhoneNumber|null
     *
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $committees;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isCommitteeMember;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isCommitteeHost;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isMailSubscriber;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOriginalId(): int
    {
        return $this->originalId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getCommittees(): array
    {
        return $this->committees ? explode('|', $this->committees) : [];
    }

    public function isCommitteeMember(): bool
    {
        return $this->isCommitteeMember;
    }

    public function isCommitteeHost(): bool
    {
        return $this->isCommitteeHost;
    }

    public function isMailSubscriber(): bool
    {
        return $this->isMailSubscriber;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
