<?php

namespace App\Entity\ThematicCommunity;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNameSlugTrait;
use App\Entity\ImageOwnerInterface;
use App\Entity\ImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThematicCommunity\ThematicCommunityRepository")
 */
class ThematicCommunity implements ImageOwnerInterface
{
    use EntityIdentityTrait;
    use EntityNameSlugTrait;
    use ImageTrait;

    public const COLORS = [
        'sante' => '#FF4D89',
        'ecole' => '#6F80FF',
        'agriculture-et-alimentation' => '#208E73',
        'europe' => '#1D5FD1',
        'pme' => '#FF6955',
        'ecologie' => '#61E9D5',
    ];

    /**
     * @var string
     *
     * @ORM\Column
     */
    #[Assert\NotBlank]
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    #[Assert\NotBlank]
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getImagePath(): string
    {
        return sprintf('images/thematic_community/banner/%s', $this->getImageName());
    }
}
