<?php

namespace AppBundle\Entity;

use AppBundle\Exception\CommitteeAlreadyApprovedException;
use AppBundle\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * This entity represents a committee group.
 *
 * @ORM\Table(
 *   name="committees",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="committee_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="committee_canonical_name_unique", columns="canonical_name"),
 *     @ORM\UniqueConstraint(name="committee_slug_unique", columns="slug")
 *   },
 *   indexes={
 *     @ORM\Index(name="committee_status_idx", columns="status")
 *   }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeRepository")
 */
class Committee implements GeoPointInterface
{
    const APPROVED = 'APPROVED';
    const PENDING = 'PENDING';
    const REFUSED = 'REFUSED';

    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;

    /**
     * The committee name.
     *
     * @ORM\Column
     */
    private $name;

    /**
     * The committee name.
     *
     * @ORM\Column
     */
    private $canonicalName;

    /**
     * The committee slug.
     *
     * @ORM\Column
     *
     * @Gedmo\Slug(fields={"canonicalName"})
     */
    private $slug;

    /**
     * The committee description.
     *
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * The committee Facebook page URL.
     *
     * @ORM\Column(nullable=true)
     */
    private $facebookPageUrl;

    /**
     * The committee Twitter nickname.
     *
     * @ORM\Column(nullable=true)
     */
    private $twitterNickname;

    /**
     * The committee Google+ page URL.
     *
     * @ORM\Column(nullable=true)
     */
    private $googlePlusPageUrl;

    /**
     * The committee current status.
     *
     * @ORM\Column(length=20)
     */
    private $status;

    /**
     * The timestamp when an administrator approved this committee.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * The adherent UUID who created this committee.
     *
     * @ORM\Column(type="uuid")
     */
    private $createdBy;

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $creator,
        string $name,
        string $description,
        PostAddress $address,
        string $slug = null,
        string $status = self::PENDING,
        string $approvedAt = null,
        string $createdAt = 'now'
    ) {
        if ($approvedAt) {
            $approvedAt = new \DateTime($approvedAt);
        }

        if ($createdAt) {
            $createdAt = new \DateTime($createdAt);
        }

        $this->uuid = $uuid;
        $this->createdBy = $creator;
        $this->setName($name);
        $this->slug = $slug;
        $this->description = $description;
        $this->postAddress = $address;
        $this->status = $status;
        $this->approvedAt = $approvedAt;
        $this->createdAt = $createdAt;
    }

    public static function createSimple(UuidInterface $uuid, string $creatorUuid, string $name, string $description, PostAddress $address): self
    {
        return new self(
            $uuid,
            Uuid::fromString($creatorUuid),
            $name,
            $description,
            $address
        );
    }

    public static function createForAdherent(Adherent $adherent, string $name, string $description, PostAddress $address): self
    {
        return new self(
            self::createUuid($name),
            clone $adherent->getUuid(),
            $name,
            $description,
            $address
        );
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getFacebookPageUrl(): ?string
    {
        return $this->facebookPageUrl;
    }

    public function getTwitterNickname(): ?string
    {
        return $this->twitterNickname;
    }

    public function getGooglePlusPageUrl(): ?string
    {
        return $this->googlePlusPageUrl;
    }

    public function isWaitingForApproval(): bool
    {
        return self::PENDING === $this->status && !$this->approvedAt;
    }

    public function isApproved(): bool
    {
        return self::APPROVED === $this->status && $this->approvedAt;
    }

    public function isRefused(): bool
    {
        return self::REFUSED === $this->status;
    }

    /**
     * Marks this committee as approved.
     *
     * This method also creates and returns the committee membership
     * relationship for the adherent who created this committee. The
     * origin adherent of this committee automatically becomes its
     * very first host member.
     *
     * @param string $timestamp The approval date and time
     *
     * @return CommitteeMembership
     */
    public function approved(string $timestamp = 'now'): CommitteeMembership
    {
        if (self::APPROVED === $this->status) {
            throw new CommitteeAlreadyApprovedException($this->uuid);
        }

        $this->status = self::APPROVED;
        $this->approvedAt = new \DateTimeImmutable($timestamp);

        return CommitteeMembership::createForHost($this->uuid, $this->createdBy, $timestamp);
    }

    public function setSocialNetworks(
        string $facebookPageUrl = null,
        string $twitterNickname = null,
        string $googlePlusPageUrl = null
    ) {
        $this->facebookPageUrl = $facebookPageUrl;
        $this->setTwitterNickname($twitterNickname);
        $this->googlePlusPageUrl = $googlePlusPageUrl;
    }

    public function setFacebookPageUrl($facebookPageUrl)
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function setTwitterNickname($twitterNickname)
    {
        $this->twitterNickname = ltrim((string) $twitterNickname, '@');
    }

    public function setGooglePlusPageUrl($googlePlusPageUrl)
    {
        $this->googlePlusPageUrl = $googlePlusPageUrl;
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

    public function isCreatedBy(UuidInterface $uuid): bool
    {
        return $this->createdBy->equals($uuid);
    }

    /**
     * Returns the approval date and time.
     *
     * @return \DateTimeImmutable|null
     */
    public function getApprovedAt(): ?\DateTimeImmutable
    {
        if ($this->approvedAt instanceof \DateTime) {
            $this->approvedAt = new \DateTimeImmutable(
                $this->approvedAt->format(DATE_ISO8601),
                $this->approvedAt->getTimezone()
            );
        }

        return $this->approvedAt;
    }
}
