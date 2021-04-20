<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Coalition\Cause;
use App\Event\EventTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CauseEventRepository")
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
class CauseEvent extends BaseEvent
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\EventCategory")
     *
     * @SymfonySerializer\Groups({"event_read", "event_list_read"})
     */
    protected $category;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Coalition\Cause", inversedBy="events")
     * @ORM\JoinTable(name="event_cause",
     *     joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE", unique=true)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="cause_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $causes;

    public function __construct(UuidInterface $uuid = null)
    {
        parent::__construct($uuid);

        $this->causes = new ArrayCollection();
    }

    public function getType(): string
    {
        return EventTypeEnum::TYPE_CAUSE;
    }

    public function getCategory(): ?EventCategory
    {
        return $this->category;
    }

    public function setCategory(EventCategory $category): void
    {
        $this->category = $category;
    }

    public function getCause(): ?Cause
    {
        return $this->causes->count() > 0 ? $this->causes->first() : null;
    }

    public function addCause(Cause $cause): void
    {
        if (!$this->causes->contains($cause)) {
            $this->causes->add($cause);
        }
    }

    public function removeCause(Cause $cause): void
    {
        $this->causes->removeElement($cause);
    }
}
