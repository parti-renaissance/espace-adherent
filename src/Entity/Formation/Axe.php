<?php

declare(strict_types=1);

namespace App\Entity\Formation;

use App\Entity\EntityMediaInterface;
use App\Entity\EntityMediaTrait;
use App\Entity\PositionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'formation_axes')]
#[UniqueEntity(fields: ['title'], message: 'Il existe déjà un axe de formation avec ce titre.')]
class Axe implements EntityMediaInterface
{
    use EntityMediaTrait;
    use PositionTrait;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string|null
     */
    #[Assert\Length(min: 2, max: 150, minMessage: 'Le titre doit pas faire plus de 150 caractères.')]
    #[Assert\NotBlank(message: 'Veuillez renseigner un titre.')]
    #[ORM\Column]
    private $title;

    /**
     * @var string|null
     */
    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(unique: true)]
    protected $slug;

    /**
     * @var string|null
     */
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Veuillez renseigner une description.'),
        new Assert\Length(min: 2, minMessage: 'La description doit faire au moins 2 caractères.'),
    ])]
    #[ORM\Column(type: 'text')]
    private $description;

    /**
     * @var string|null
     */
    #[Assert\NotBlank(message: 'Veuillez renseigner un contenu.')]
    #[ORM\Column(type: 'text')]
    private $content;

    /**
     * @var Path
     */
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Path::class, inversedBy: 'axes')]
    private $path;

    /**
     * @var Module[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'axe', targetEntity: Module::class)]
    private $modules;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->path ? $this->path->getTitle().', '.$this->title : '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function getPath(): ?Path
    {
        return $this->path;
    }

    public function setPath(?Path $path): void
    {
        $this->path = $path;
    }
}
