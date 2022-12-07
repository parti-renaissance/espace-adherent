<?php

namespace App\Entity\AdherentFormation;

use App\Entity\PositionTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentFormation\FormationRepository");
 * @ORM\Table(name="adherent_formation")
 *
 * @UniqueEntity(fields={"title"}, message="adherent_formation.title.unique_entity")
 */
class Formation
{
    use PositionTrait;

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank(message="Veuillez renseigner un titre.")
     * @Assert\Length(allowEmptyString=true, min=2, minMessage="Le titre doit faire au moins 2 caractères.")
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(allowEmptyString=true, min=2, minMessage="La description doit faire au moins 2 caractères.")
     */
    private ?string $description = null;

    /**
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\AdherentFormation\File",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     *
     * @Assert\Valid
     */
    private ?File $file = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $visible = false;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    protected $downloadsCount = 0;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getDownloadsCount(): int
    {
        return $this->downloadsCount;
    }

    public function incrementDownloadsCount(): void
    {
        ++$this->downloadsCount;
    }
}
