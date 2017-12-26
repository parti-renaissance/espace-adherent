<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseEventCategory
{
    const ENABLED = 'ENABLED';
    const DISABLED = 'DISABLED';

    /**
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
     * @Algolia\Attribute
     */
    protected $name = '';

    /**
     * @ORM\Column(length=10, options={"default"="ENABLED"})
     * @Algolia\Attribute
     */
    protected $status;

    public function __construct(?string $name = null, ?string $status = self::ENABLED)
    {
        if ($name) {
            $this->name = $name;
        }
        $this->status = $status;
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
}
