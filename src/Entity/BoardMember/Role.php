<?php

namespace AppBundle\Entity\BoardMember;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="roles",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="board_member_role_code_unique", columns="code")
 *   }
 * )
 *
 * @UniqueEntity("code")
 * @UniqueEntity("maleName")
 * @UniqueEntity("femaleName")
 */
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
     *
     * @Assert\NotBlank
     * @Assert\Length(max="20")
     */
    private $code;

    /**
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     */
    private $maleName = '';

    /**
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max="100")
     */
    private $femaleName = '';

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\BoardMember\BoardMember", mappedBy="roles")
     *
     * @var BoardMember[]
     */
    private $boardMembers;

    public function __construct(?string $code = null, ?string $maleName = null, ?string $femaleName = null)
    {
        $this->code = (string) $code;
        $this->maleName = (string) $maleName;
        $this->femaleName = (string) $femaleName;
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

    public function getMaleName(): string
    {
        return $this->maleName;
    }

    public function setMaleName(string $maleName): void
    {
        $this->maleName = $maleName;
    }

    public function getFemaleName(): string
    {
        return $this->maleName;
    }

    public function setFemaleName(string $femaleName): void
    {
        $this->femaleName = $femaleName;
    }

    public function addBoardMember(BoardMember $boardMember): void
    {
        if (!$this->boardMembers->contains($boardMember)) {
            $this->boardMembers->add($boardMember);
        }
    }
}
