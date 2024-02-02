<?php

namespace App\Entity\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventGroupCategoryRepository")
 *
 * @UniqueEntity("name")
 */
class EventGroupCategory extends BaseEventCategory
{
    public const CAMPAIGN_EVENTS = 'evenements-de-campagne';

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event\EventCategory", mappedBy="eventGroupCategory", fetch="EAGER")
     * @ORM\OrderBy({"name": "ASC"})
     */
    private $eventCategories;

    public function __construct(?string $name = null, ?string $status = self::ENABLED, ?string $slug = null)
    {
        parent::__construct($name, $status, $slug);
        $this->eventCategories = new ArrayCollection();
    }

    public function getEventCategories(): Collection
    {
        return $this->eventCategories;
    }

    public function addEventCategory(EventCategory $eventCategory): void
    {
        if (!$this->eventCategories->contains($eventCategory)) {
            $this->eventCategories[] = $eventCategory;
            $eventCategory->setEventGroupCategory($this);
        }
    }

    public function removeEventCategory(EventCategory $eventCategory): void
    {
        $this->eventCategories->removeElement($eventCategory);
        if ($eventCategory->getEventGroupCategory() === $this) {
            $eventCategory->setEventGroupCategory(null);
        }
    }
}
