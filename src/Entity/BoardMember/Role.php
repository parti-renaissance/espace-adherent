<?php

namespace App\Entity\BoardMember;

use App\Repository\BoardMember\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity("code")
 * @UniqueEntity("name")
 */
#[ORM\Table(name: 'roles')]
#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max="20")
     */
    #[ORM\Column(length: 20, unique: true)]
    private $code;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     */
    #[ORM\Column(length: 100, unique: true)]
    private $name = '';

    /**
     * @var BoardMember[]
     */
    #[ORM\ManyToMany(targetEntity: BoardMember::class, mappedBy: 'roles')]
    private $boardMembers;

    public function __construct(?string $code = null, ?string $name = null)
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
