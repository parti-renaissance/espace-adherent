<?php

namespace App\Entity\Biography;

use App\Entity\EntitySourceableInterface;
use App\Entity\EntitySourceableTrait;
use App\Repository\Biography\ExecutiveOfficeMemberRepository;
use App\Validator\UniqueExecutiveOfficeMemberRole;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueExecutiveOfficeMemberRole
 */
#[ORM\Entity(repositoryClass: ExecutiveOfficeMemberRepository::class)]
#[ORM\Table(name: 'biography_executive_office_member')]
class ExecutiveOfficeMember extends AbstractBiography implements EntitySourceableInterface
{
    use EntitySourceableTrait;

    #[Assert\Length(max: 255)]
    #[ORM\Column]
    private $job;

    #[ORM\Column(nullable: true)]
    private ?string $role;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $president = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $executiveOfficer = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $deputyGeneralDelegate = false;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $description = null,
        ?string $content = null,
        ?bool $published = null,
        ?string $job = null,
        ?string $role = null
    ) {
        parent::__construct($uuid, $firstName, $lastName, $description, $content, $published);

        $this->job = $job;
        $this->role = $role;
    }

    public function isExecutiveOfficer(): ?bool
    {
        return ExecutiveOfficeRoleEnum::EXECUTIVE_OFFICER === $this->role;
    }

    public function isPresident(): ?bool
    {
        return ExecutiveOfficeRoleEnum::PRESIDENT === $this->role;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(string $job): void
    {
        $this->job = $job;
    }

    public function getImagePath(): string
    {
        return sprintf('images/biographies/notre-organisation/%s', $this->getImageName());
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): void
    {
        $this->role = $role;
    }
}
