<?php

namespace AppBundle\Entity\ElectedRepresentative;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ElectedRepresentative\ZoneRepository")
 * @ORM\Table(
 *     name="elected_representative_zone",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="elected_representative_zone_name_category_unique", columns={"name", "category_id"})
 *     })
 */
class Zone
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @SymfonySerializer\Groups({"autocomplete"})
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"autocomplete"})
     */
    private $name;

    /**
     * @var ZoneCategory|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ElectedRepresentative\ZoneCategory", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    public function __construct(ZoneCategory $category = null, string $name = null)
    {
        $this->category = $category;
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCategory(): ?ZoneCategory
    {
        return $this->category;
    }

    public function setCategory(ZoneCategory $category): void
    {
        $this->category = $category;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
