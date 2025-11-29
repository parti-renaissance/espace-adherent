<?php

declare(strict_types=1);

namespace App\Entity\Event;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\EventCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['event_category_read']],
    order: ['slug' => 'ASC'],
    paginationEnabled: false
)]
#[ORM\Entity(repositoryClass: EventCategoryRepository::class)]
#[ORM\Table(name: 'events_categories')]
#[UniqueEntity(fields: ['name'])]
class EventCategory extends BaseEventCategory
{
    /**
     * @var EventGroupCategory|null
     */
    #[Assert\NotBlank]
    #[Groups(['event_read', 'event_list_read', 'event_category_read'])]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: EventGroupCategory::class, inversedBy: 'eventCategories')]
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
