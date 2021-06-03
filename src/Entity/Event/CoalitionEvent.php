<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Coalition\Coalition;
use App\Event\EventTypeEnum;
use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition", inversedBy="events")
     *
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"event_write", "event_read", "event_list_read"})
     */
    private $coalition;

    public function getType(): string
    {
        return EventTypeEnum::TYPE_COALITION;
    }

    public function getCoalition(): ?Coalition
    {
        return $this->coalition;
    }

    public function setCoalition(Coalition $coalition): void
    {
        $this->coalition = $coalition;
    }
}
