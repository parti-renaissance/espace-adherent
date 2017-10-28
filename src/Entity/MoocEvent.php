<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MoocEventRepository")
 */
class MoocEvent extends BaseEvent
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Group")
     *
     * @Algolia\Attribute
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MoocEventCategory")
     *
     * @Algolia\Attribute
     */
    private $moocEventCategory;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $wasPublished = false;

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $organizer,
        ?Group $group,
        string $name,
        MoocEventCategory $category,
        string $description,
        PostAddress $address,
        \DateTime $beginAt,
        \DateTime $finishAt,
        int $capacity = null,
        int $participantsCount = 0
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->group = $group;
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
        MoocEventCategory $category,
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

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function getCategory(): MoocEventCategory
    {
        return $this->moocEventCategory;
    }

    public function setCategory(MoocEventCategory $category): MoocEvent
    {
        $this->moocEventCategory = $category;

        return $this;
    }

    public function getMoocEventCategory(): MoocEventCategory
    {
        return $this->moocEventCategory;
    }

    public function setMoocEventeCategory(MoocEventCategory $category): MoocEvent
    {
        $this->moocEventCategory = $category;

        return $this;
    }

    public function getType(): string
    {
        return self::MOOC_EVENT_TYPE;
    }

    public function wasPublished(): bool
    {
        return true === $this->wasPublished;
    }

    public function setWasPublished(bool $wasPublished): MoocEvent
    {
        $this->wasPublished = $wasPublished;

        return $this;
    }
}
