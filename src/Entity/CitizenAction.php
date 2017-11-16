<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenActionRepository")
 */
class CitizenAction extends BaseEvent
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CitizenProject")
     *
     * @Algolia\Attribute
     */
    private $citizenProject;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CitizenActionCategory")
     *
     * @Algolia\Attribute
     */
    private $citizenActionCategory;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $wasPublished = false;

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
        int $capacity = null,
        int $participantsCount = 0
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->citizenProject = $citizenProject;
        $this->setName($name);
        $this->category = $category;
        $this->description = $description;
        $this->postAddress = $address;
        $this->capacity = $capacity;
        $this->participantsCount = $participantsCount;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->status = self::STATUS_SCHEDULED;
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
        string $beginAt,
        string $finishAt,
        int $capacity = null
    ) {
        $this->setName($name);
        $this->category = $category;
        $this->capacity = $capacity;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->description = $description;

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
        return $this->citizenActionCategory;
    }

    public function setCategory(CitizenActionCategory $category): self
    {
        $this->citizenActionCategory = $category;

        return $this;
    }

    public function getCitizenActionCategory(): CitizenActionCategory
    {
        return $this->citizenActionCategory;
    }

    public function setCitizenActioneCategory(CitizenActionCategory $category): self
    {
        $this->citizenActionCategory = $category;

        return $this;
    }

    public function getType(): string
    {
        return self::CITIZEN_ACTION_TYPE;
    }

    public function wasPublished(): bool
    {
        return true === $this->wasPublished;
    }

    public function setWasPublished(bool $wasPublished): self
    {
        $this->wasPublished = $wasPublished;

        return $this;
    }
}
