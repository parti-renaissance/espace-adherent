<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Address\GeoCoder;
use AppBundle\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenActionRepository")
 */
class CitizenAction extends BaseEvent
{
    /**
     * @ORM\ManyToOne(targetEntity="CitizenActionCategory")
     *
     * @Algolia\Attribute
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="CitizenProject")
     *
     * @Algolia\Attribute
     */
    private $citizenProject;

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $organizer,
        CitizenProject $citizenProject,
        string $name,
        CitizenActionCategory $category,
        string $description,
        PostAddress $address,
        \DateTimeInterface $beginAt,
        \DateTimeInterface $finishAt,
        int $participantsCount = 0,
        array $referentTags = [],
        string $timeZone = GeoCoder::DEFAULT_TIME_ZONE
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->citizenProject = $citizenProject;
        $this->setName($name);
        $this->category = $category;
        $this->description = $description;
        $this->postAddress = $address;
        $this->participantsCount = $participantsCount;
        // We need a \DateTime object for now to work with Gedmo sluggable
        $this->beginAt = $beginAt instanceof \DateTimeImmutable ? new \DateTime($beginAt->format(\DATE_ATOM)) : $beginAt;
        $this->finishAt = $finishAt;
        $this->status = self::STATUS_SCHEDULED;
        $this->referentTags = new ArrayCollection($referentTags);
        $this->timeZone = $timeZone;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function update(
        string $name,
        CitizenActionCategory $category,
        string $description,
        PostAddress $address,
        \DateTimeInterface $beginAt,
        \DateTimeInterface $finishAt,
        string $timeZone
    ) {
        $this->setName($name);
        $this->category = $category;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->description = $description;
        $this->timeZone = $timeZone;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }

    public function getCategory(): CitizenActionCategory
    {
        return $this->category;
    }

    public function setCategory(CitizenActionCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getType(): string
    {
        return self::CITIZEN_ACTION_TYPE;
    }

    public function getReportType(): string
    {
        return ReportType::CITIZEN_ACTION;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("categoryName")
     * @JMS\Groups({"public", "citizen_action_read"})
     */
    public function getCitizenActionCategoryName(): ?string
    {
        if (!$category = $this->getCategory()) {
            return null;
        }

        return $category->getName();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("citizenProjectUuid")
     * @JMS\Groups({"public", "citizen_action_read"})
     */
    public function getCitizenProjectUuidAsString(): ?string
    {
        if (!$citizenProject = $this->getCitizenProject()) {
            return null;
        }

        return $citizenProject->getUuidAsString();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("organizerUuid")
     * @JMS\Groups({"public", "citizen_action_read"})
     */
    public function getOrganizerUuid(): ?string
    {
        if (!$organizer = $this->getOrganizer()) {
            return null;
        }

        return $organizer->getUuidAsString();
    }
}
