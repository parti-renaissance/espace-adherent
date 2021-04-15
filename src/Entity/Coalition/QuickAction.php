<?php

namespace App\Entity\Coalition;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_enabled": false,
 *         "normalization_context": {"groups": {"quick_action_read"}},
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 *
 * @ORM\Table(name="cause_quick_action")
 * @ORM\Entity
 */
class QuickAction
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     *
     * @SymfonySerializer\Groups({"quick_action_read"})
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=100)
     *
     * @SymfonySerializer\Groups({"quick_action_read", "cause_update"})
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     * @Assert\Url
     *
     * @SymfonySerializer\Groups({"quick_action_read", "cause_update"})
     */
    private $url;

    /**
     * @var Cause|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Cause", inversedBy="quickActions")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull
     */
    private $cause;

    public function __construct(string $title = null, string $url = null, Cause $cause = null)
    {
        $this->title = $title;
        $this->url = $url;
        $this->cause = $cause;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getCause(): ?Cause
    {
        return $this->cause;
    }

    public function setCause(Cause $cause): void
    {
        $this->cause = $cause;
    }
}
