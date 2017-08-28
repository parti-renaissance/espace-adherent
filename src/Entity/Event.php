<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 * @Algolia\Index
 */
class Event extends BaseEvent
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     *
     * @Algolia\Attribute
     */
    private $committee;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EventCategory")
     *
     * @Algolia\Attribute
     */
    private $category;

    private $type;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isForLegislatives = false;

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $organizer,
        ?Committee $committee,
        string $name,
        EventCategory $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt,
        int $capacity = null,
        bool $isForLegislatives = false,
        string $createdAt = null,
        int $participantsCount = 0,
        string $slug = null,
        string $type = null
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->committee = $committee;
        $this->setName($name);
        $this->slug = $slug;
        $this->category = $category;
        $this->description = $description;
        $this->postAddress = $address;
        $this->capacity = $capacity;
        $this->participantsCount = $participantsCount;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->createdAt = new \DateTime($createdAt ?: 'now');
        $this->updatedAt = new \DateTime($createdAt ?: 'now');
        $this->status = self::STATUS_SCHEDULED;
        $this->isForLegislatives = $isForLegislatives;
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function update(
        string $name,
        EventCategory $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt,
        int $capacity = null,
        bool $isForLegislatives = false
    ) {
        $this->setName($name);
        $this->category = $category;
        $this->capacity = $capacity;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->description = $description;
        $this->isForLegislatives = $isForLegislatives;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    public function getCategory(): EventCategory
    {
        return $this->category;
    }

    public function getType(): ?string
    {
        return $this->type ?? 'event';
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function isForLegislatives()
    {
        return $this->isForLegislatives;
    }
}
