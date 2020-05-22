<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="user_list_definition_type_code_unique", columns={"type", "code"})
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
class UserListDefinition
{
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
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Entity\UserListDefinitionEnum", "getTypes"})
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     */
    private $label;

    public function __construct(string $type, string $code, string $label)
    {
        $this->type = $type;
        $this->code = $code;
        $this->label = $label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
