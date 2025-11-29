<?php

declare(strict_types=1);

namespace App\Entity\ElectedRepresentative;

use App\Repository\ElectedRepresentative\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
#[ORM\Index(columns: ['code'], name: 'elected_repr_zone_code')]
#[ORM\Table(name: 'elected_representative_zone')]
#[ORM\UniqueConstraint(name: 'elected_representative_zone_name_category_unique', columns: ['name', 'category_id'])]
class Zone
{
    /**
     * @var int|null
     */
    #[Groups(['autocomplete'])]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string|null
     */
    #[Groups(['autocomplete'])]
    #[ORM\Column]
    private $name;

    /**
     * @var ZoneCategory|null
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: ZoneCategory::class, fetch: 'EAGER')]
    private $category;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $code;

    /**
     * @var Collection
     */
    #[ORM\InverseJoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[ORM\JoinColumn(name: 'child_id', referencedColumnName: 'id')]
    #[ORM\JoinTable(name: 'elected_representative_zone_parent')]
    #[ORM\ManyToMany(targetEntity: Zone::class, inversedBy: 'children')]
    private $parents;

    /**
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: Zone::class, mappedBy: 'parents')]
    private $children;

    public function __construct(?ZoneCategory $category = null, ?string $name = null)
    {
        $this->category = $category;
        $this->name = $name;
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChildren(self $zone): void
    {
        if (!$this->children->contains($zone)) {
            $this->children->add($zone);
        }
    }
}
