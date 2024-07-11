<?php

namespace App\Entity\ReferentOrganizationalChart;

use App\Repository\ReferentOrganizationalChart\OrganizationalChartItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'organizational_chart_item')]
#[ORM\Entity(repositoryClass: OrganizationalChartItemRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string', length: 20)]
#[ORM\DiscriminatorMap(['person_orga_item' => PersonOrganizationalChartItem::class, 'group_orga_item' => GroupOrganizationalChartItem::class])]
#[Gedmo\Tree(type: 'nested')]
abstract class AbstractOrganizationalChartItem
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column]
    private $label;

    /**
     * @var int
     */
    #[ORM\Column(name: 'lft', type: 'integer')]
    #[Gedmo\TreeLeft]
    private $lft;

    /**
     * @var int
     */
    #[ORM\Column(name: 'lvl', type: 'integer')]
    #[Gedmo\TreeLevel]
    private $lvl;

    /**
     * @var int
     */
    #[ORM\Column(name: 'rgt', type: 'integer')]
    #[Gedmo\TreeRight]
    private $rgt;

    /**
     * @var AbstractOrganizationalChartItem
     */
    #[ORM\JoinColumn(name: 'tree_root', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AbstractOrganizationalChartItem::class)]
    #[Gedmo\TreeRoot]
    private $root;

    /**
     * @var AbstractOrganizationalChartItem
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AbstractOrganizationalChartItem::class, cascade: ['persist'], inversedBy: 'children')]
    #[Gedmo\TreeParent]
    private $parent;

    /**
     * @var Collection|AbstractOrganizationalChartItem[]
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: AbstractOrganizationalChartItem::class, cascade: ['persist'])]
    private $children;

    public function __construct(?string $label = null, ?self $parent = null)
    {
        $this->children = new ArrayCollection();
        $this->label = $label;
        $this->parent = $parent;
    }

    abstract public function getTypeLabel(): string;

    public function __toString()
    {
        return sprintf('(%s) %s', $this->getTypeLabel(), $this->label);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return Collection|AbstractOrganizationalChartItem[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    public function addChild(self $child): void
    {
        $child->setParent($this);
        $this->children[] = $child;
    }

    public function removeChild(self $child): void
    {
        $child->setParent(null);
        $this->children[] = $child;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    public function getLevel(): int
    {
        if ($this->getParent()) {
            return $this->getParent()->getLevel() + 1;
        }

        return 1;
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function setLft(int $lft): void
    {
        $this->lft = $lft;
    }

    public function getLvl(): int
    {
        return $this->lvl;
    }

    public function setLvl(int $lvl): void
    {
        $this->lvl = $lvl;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    public function setRgt(int $rgt): void
    {
        $this->rgt = $rgt;
    }

    public function getRoot(): self
    {
        return $this->root;
    }

    public function setRoot(self $root): void
    {
        $this->root = $root;
    }
}
