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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {"groups": {"event_read", "image_owner_exposed"}},
 *         "pagination_client_items_per_page": true,
 *         "order": {"beginAt": "DESC"},
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\CoalitionEventRepository")
 */
class CoalitionEvent extends BaseEvent
{
    use DefaultCategoryOwnerTrait;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Coalition\Coalition", inversedBy="events")
     * @ORM\JoinTable(name="event_coalition",
     *     joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE", unique=true)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="coalition_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     *
     * @Assert\Count(min=1, max=1, exactMessage="coalition_event.coalition.invalid")
     *
     * @SymfonySerializer\Groups({"event_write"})
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
