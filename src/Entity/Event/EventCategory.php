<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\EventCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_enabled": false,
 *         "order": {"slug": "ASC"},
 *         "normalization_context": {
 *             "groups": {"event_category_read"}
 *         },
 *     },
 *     itemOperations={"get"},
 *     collectionOperations={
 *         "get": {
 *             "path": "/event_categories",
 *         },
 *     }
 * )
 */
#[ORM\Table(name: 'events_categories')]
#[ORM\Entity(repositoryClass: EventCategoryRepository::class)]
#[UniqueEntity(fields: ['name'])]
class EventCategory extends BaseEventCategory
{
    /**
     * @var EventGroupCategory|null
     */
    #[Groups(['event_read', 'event_list_read', 'event_category_read'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: EventGroupCategory::class, inversedBy: 'eventCategories')]
    #[Assert\NotBlank]
    private $eventGroupCategory;

    public function getEventGroupCategory(): ?EventGroupCategory
    {
        return $this->eventGroupCategory;
    }

    public function setEventGroupCategory(EventGroupCategory $eventGroupCategory): void
    {
        $this->eventGroupCategory = $eventGroupCategory;
    }
}
