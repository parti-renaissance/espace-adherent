<?php

namespace App\Entity\ElectedRepresentative;

use App\Entity\EntityReferentTagTrait;
use App\Entity\ReferentTag;
use App\Repository\ElectedRepresentative\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'elected_representative_zone')]
#[ORM\Index(columns: ['code'], name: 'elected_repr_zone_code')]
#[ORM\UniqueConstraint(name: 'elected_representative_zone_name_category_unique', columns: ['name', 'category_id'])]
#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{
    use EntityReferentTagTrait;

    /**
     * @var int|null
     */
    #[Groups(['autocomplete'])]
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
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
     * @var Collection|ReferentTag[]
     */
    #[ORM\JoinTable(name: 'elected_representative_zone_referent_tag')]
    #[ORM\JoinColumn(name: 'elected_representative_zone_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'referent_tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: ReferentTag::class)]
    protected $referentTags;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $code;

    /**
     * @var Collection
     */
    #[ORM\JoinTable(name: 'elected_representative_zone_parent')]
    #[ORM\JoinColumn(name: 'child_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'parent_id', referencedColumnName: 'id')]
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
        $this->referentTags = new ArrayCollection();
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
