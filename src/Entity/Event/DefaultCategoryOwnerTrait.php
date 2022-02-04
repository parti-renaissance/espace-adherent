<?php

namespace App\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait DefaultCategoryOwnerTrait
{
    /**
     * @var EventCategoryInterface|EventCategory|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\EventCategory")
     *
     * @Groups({"event_read", "event_list_read", "event_write"})
     */
    protected $category;

    public function getCategory(): ?EventCategoryInterface
    {
        return $this->category;
    }

    public function setCategory(?EventCategoryInterface $category): void
    {
        $this->category = $category;
    }

    public function getCategoryName(): string
    {
        return $this->category ? $this->category->getName() : '';
    }
}
