<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubscriptionTypeRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"code"})})
 *
 * @UniqueEntity(fields={"code"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class SubscriptionType
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $code;

    public function __construct(string $label = null, string $code = null)
    {
        $this->label = $label;
        $this->code = $code;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }
}
