<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Exception\CitizenProjectAlreadyApprovedException;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CitizenProjectCategory")
     *
     * @Algolia\Attribute
     */
    private $category;

    /**
     * @ORM\Column
     *
     * @Algolia\Attribute
     */
    protected $subtitle;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=500)
     */
    private $problemDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=800)
     */
    private $proposedSolution;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=500)
     */
    private $requiredMeans;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $assistanceNeeded = false;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $assistanceContent;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     *
     * @Algolia\Attribute
     */
    private $committee;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize = "1M",
     *     mimeTypes = {"image/jpeg", "image/png"},
     * )
     */
    private $image;

    /**
     * A cached list of the administrators (for admin).
     */
    public $administrators = [];

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $creator,
        string $name,
        string $subtitle,
        CitizenProjectCategory $category,
        ?Committee $committee,
        bool $assistanceNeeded = false,
        string $problemDescription = '',
        string $proposedSolution = '',
        string $requiredMeans = '',
        PhoneNumber $phone = null,
        NullablePostAddress $address = null,
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
        $this->committee = $committee;
        $this->setName($name);
        $this->slug = $slug;
        $this->category = $category;
        $this->subtitle = $subtitle;
        $this->postAddress = $address;
        $this->phone = $phone;
        $this->assistanceNeeded = $assistanceNeeded;
        $this->status = $status;
        $this->membersCounts = $membersCount;
        $this->approvedAt = $approvedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
        $this->problemDescription = $problemDescription;
        $this->proposedSolution = $proposedSolution;
        $this->requiredMeans = $requiredMeans;
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

    public function getCategory(): CitizenProjectCategory
    {
        return $this->category;
    }

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setSubtitle(string $subtitle)
    {
        $this->subtitle = $subtitle;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function isAssistanceNeeded(): bool
    {
        return $this->assistanceNeeded;
    }

    /**
     * @return string
     */
    public function getAssistanceContent(): ?string
    {
        return $this->assistanceContent;
    }

    /**
     * @param string $assistanceContent
     */
    public function setAssistanceContent(?string $assistanceContent): void
    {
        $this->assistanceContent = $assistanceContent;
    }

    public function setAssistanceNeeded(bool $assistanceNeeded): void
    {
        $this->assistanceNeeded = $assistanceNeeded;
    }

    public function setProblemDescription(?string $problemDescription): void
    {
        $this->problemDescription = $problemDescription;
    }

    public function getProblemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function setProposedSolution(?string $proposedSolution): void
    {
        $this->proposedSolution = $proposedSolution;
    }

    public function getProposedSolution(): ?string
    {
        return $this->proposedSolution;
    }

    public function setRequiredMeans(?string $requiredMeans): void
    {
        $this->requiredMeans = $requiredMeans;
    }

    public function getRequiredMeans(): ?string
    {
        return $this->requiredMeans;
    }

    public static function createForAdherent(
        Adherent $adherent,
        string $name,
        string $subtitle,
        CitizenProjectCategory $category,
        PhoneNumber $phone,
        string $assistanceNeeded,
        string $problemDescription,
        string $proposedSolution,
        string $requiredMeans,
        Committee $committee = null,
        NullablePostAddress $address = null,
        string $createdAt = 'now'
    ): self {
        $citizenProject = new self(
            self::createUuid($name),
            clone $adherent->getUuid(),
            $name,
            $subtitle,
            $category,
            $committee,
            $assistanceNeeded,
            $problemDescription,
            $proposedSolution,
            $requiredMeans,
            $phone,
            $address
        );

        $citizenProject->createdAt = new \DateTime($createdAt);
        $citizenProject->status = self::PENDING;

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

    public function update(string $name, NullablePostAddress $address): void
    {
        $this->setName($name);
        $this->setSubtitle($subtitle);
        $this->category = $category;
        $this->setAssistanceNeeded($assistanceNeeded);
        $this->setProblemDescription($problemDescription);
        $this->setProposedSolution($proposedSolution);
        $this->setRequiredMeans($requiredMeans);

        if (null === $this->postAddress || !$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }
}
