<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Coalition\Cause;
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
 * @ORM\Entity(repositoryClass="App\Repository\CauseEventRepository")
 */
class CauseEvent extends BaseEvent
{
    use DefaultCategoryOwnerTrait;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Coalition\Cause", inversedBy="events")
     * @ORM\JoinTable(name="event_cause",
     *     joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE", unique=true)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="cause_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     *
     * @Assert\Count(min=1, max=1, exactMessage="cause_event.cause.invalid")
     *
     * @SymfonySerializer\Groups({"event_write"})
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

    /**
     * @return Cause[]|Collection
     */
    public function getCauses(): Collection
    {
        return $this->causes;
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
