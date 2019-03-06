<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventGroupCategoryRepository")
 * @ORM\Table(
 *     name="events_groups_categories",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="event_group_category_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="event_group_category_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index
 */
class EventGroupCategory extends BaseEventCategory
{
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\EventCategory", mappedBy="eventGroupCategory")
     */
    private $eventCategories;

    public function __construct(?string $name = null, ?string $status = self::ENABLED, string $slug = null)
    {
        parent::__construct($name, $status, $slug);
        $this->eventCategories = new ArrayCollection();
    }

    public function getChampionshipCompetitors(): Collection
    {
        return $this->eventCategories;
    }

    public function addChampionshipCompetitor(EventCategory $eventCategory): self
    {
        if (!$this->eventCategories->contains($eventCategory)) {
            $this->eventCategories[] = $eventCategory;
            $eventCategory->setEventGroupCategory($this);
        }

        return $this;
    }

    public function removeEventCategory(EventCategory $eventCategory): self
    {
        if ($this->eventCategories->contains($eventCategory)) {
            $this->eventCategories->removeElement($eventCategory);
            if ($eventCategory->getEventGroupCategory() === $this) {
                $eventCategory->setEventGroupCategory(null);
            }
        }

        return $this;
    }
}
