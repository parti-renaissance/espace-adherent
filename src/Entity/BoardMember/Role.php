<?php

namespace App\Entity\BoardMember;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BoardMember\RoleRepository")
 * @ORM\Table(name="roles")
 */
#[UniqueEntity('code')]
#[UniqueEntity('name')]
class Role
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(length=20, unique=true)
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: '20')]
    private $code;

    /**
     * @ORM\Column(length=100, unique=true)
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: '100')]
    private $name = '';

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\BoardMember\BoardMember", mappedBy="roles")
     *
     * @var BoardMember[]
     */
    private $boardMembers;

    public function __construct(string $code = null, string $name = null)
    {
        $this->code = (string) $code;
        $this->name = (string) $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function addBoardMember(BoardMember $boardMember): void
    {
        if (!$this->boardMembers->contains($boardMember)) {
            $this->boardMembers->add($boardMember);
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
