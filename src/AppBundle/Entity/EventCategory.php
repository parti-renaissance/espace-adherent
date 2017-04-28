<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="events_categories",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="event_category_name_unique", columns="name")
 *   }
 * )
 *
 * @UniqueEntity("name")
 */
class EventCategory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     */
    private $name = '';

    public function __construct(?string $name = null)
    {
        if ($name) {
            $this->name = $name;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
