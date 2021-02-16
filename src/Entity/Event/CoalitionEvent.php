<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Coalition\Coalition;
use App\Event\EventTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CoalitionEventRepository")
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {"groups": {"event_read"}},
 *         "pagination_client_items_per_page": true,
 *         "order": {"beginAt": "DESC"},
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 */
class CoalitionEvent extends BaseEvent
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\EventCategory")
     *
     * @SymfonySerializer\Groups({"event_read"})
     */
    protected $category;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Coalition\Coalition", inversedBy="events")
     */
    private $coalitions;

    public function __construct(UuidInterface $uuid = null)
    {
        parent::__construct($uuid);

        $this->coalitions = new ArrayCollection();
    }

    public function getType(): string
    {
        return EventTypeEnum::TYPE_COALITION;
    }

    public function getCategory(): ?EventCategory
    {
        return $this->category;
    }

    public function setCategory(EventCategory $category): void
    {
        $this->category = $category;
    }

    /**
     * @return Coalition[]|Collection
     */
    public function getCoalitions(): Collection
    {
        return $this->coalitions;
    }

    public function addCoalition(Coalition $coalition): void
    {
        if (!$this->coalitions->contains($coalition)) {
            $this->coalitions->add($coalition);
        }
    }

    public function removeCoalition(Coalition $coalition): void
    {
        $this->coalitions->removeElement($coalition);
    }
}
