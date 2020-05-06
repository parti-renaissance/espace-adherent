<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventCategoryRepository")
 * @ORM\Table(
 *     name="events_categories",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="event_category_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="event_category_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index
 */
class EventCategory extends BaseEventCategory
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EventGroupCategory", inversedBy="eventCategories")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
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
