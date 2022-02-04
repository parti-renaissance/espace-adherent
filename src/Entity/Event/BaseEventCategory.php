<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseEventCategory implements EventCategoryInterface
{
    public const ENABLED = 'ENABLED';
    public const DISABLED = 'DISABLED';

    /**
     * @ApiProperty(identifier=false)
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     *
     * @SymfonySerializer\Groups({"event_read", "event_list_read", "event_category_read"})
     */
    protected $name = '';

    /**
     * @ApiProperty(identifier=true)
     *
     * @var string|null
     *
     * @ORM\Column(length=100, unique=true)
     * @Gedmo\Slug(fields={"name"}, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     *
     * @SymfonySerializer\Groups({"event_read", "event_list_read", "event_category_read"})
     */
    protected $slug;

    /**
     * @ORM\Column(length=10, options={"default": "ENABLED"})
     */
    protected $status;

    public function __construct(?string $name = null, ?string $status = self::ENABLED, string $slug = null)
    {
        if ($name) {
            $this->name = $name;
        }
        $this->status = $status;
        $this->slug = $slug;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
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

    public function isVisible(): bool
    {
        return self::ENABLED === $this->status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }
}
