<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CmsBlockRepository")
 */
#[UniqueEntity('name', message: 'cms_block.name.unique')]
class CmsBlock implements EntityAdministratorBlameableInterface
{
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, unique=true)
     */
    #[Assert\NotBlank(message: 'cms_block.name.not_blank')]
    #[Assert\Length(max: '100', maxMessage: 'cms_block.name.max_length')]
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    #[Assert\Length(max: '255', maxMessage: 'cms_block.description.max_length')]
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    public function __construct(string $name = null, string $description = null, string $content = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->content = $content;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}
