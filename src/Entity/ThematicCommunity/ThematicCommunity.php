<?php

namespace App\Entity\ThematicCommunity;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNameSlugTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class ThematicCommunity
{
    use EntityIdentityTrait;
    use EntityNameSlugTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
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
}
