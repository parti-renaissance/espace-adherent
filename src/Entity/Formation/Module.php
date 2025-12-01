<?php

declare(strict_types=1);

namespace App\Entity\Formation;

use App\Entity\EntityMediaInterface;
use App\Entity\EntityMediaTrait;
use App\Entity\PositionTrait;
use App\Repository\Formation\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
#[ORM\Table(name: 'formation_modules')]
#[UniqueEntity(fields: ['title', 'axe'], message: 'module.title.unique_entity')]
#[UniqueEntity(fields: ['slug', 'axe'], message: 'module.slug.unique_entity')]
class Module implements \Stringable, EntityMediaInterface
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
    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Veuillez renseigner un titre.'),
        new Assert\Length(min: 2, minMessage: 'Le titre doit faire au moins 2 caractères.'),
    ])]
    #[ORM\Column(unique: true)]
    private $title;

    /**
     * @var string|null
     */
    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(unique: true)]
    private $slug;

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
     * @var Axe|null
     */
    #[Assert\NotBlank(message: 'Veuillez renseigner un axe.')]
    #[ORM\ManyToOne(targetEntity: Axe::class, inversedBy: 'modules')]
    private $axe;

    /**
     * @var Collection|File[]
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'module', targetEntity: File::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->title;
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

    public function getAxe(): ?Axe
    {
        return $this->axe;
    }

    public function setAxe(Axe $axe): void
    {
        $this->axe = $axe;
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): void
    {
        if (!$this->files->contains($file)) {
            $file->setModule($this);
            $this->files->add($file);
        }
    }

    public function removeFile(File $file): void
    {
        $this->files->removeElement($file);
    }
}
