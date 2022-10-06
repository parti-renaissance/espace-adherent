<?php

namespace App\Entity\Biography;

use App\Entity\EntitySourceableInterface;
use App\Entity\EntitySourceableTrait;
use App\Validator\UniqueExecutiveOfficeMemberRole;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Biography\ExecutiveOfficeMemberRepository")
 * @ORM\Table(name="biography_executive_office_member")
 *
 * @UniqueExecutiveOfficeMemberRole
 */
class ExecutiveOfficeMember extends AbstractBiography implements EntitySourceableInterface
{
    use EntitySourceableTrait;

    /**
     * @ORM\Column
     *
     * @Assert\Length(max=255)
     */
    private $job;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $president = false;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $executiveOfficer = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $deputyGeneralDelegate = false;

    public function __construct(
        UuidInterface $uuid = null,
        string $firstName = null,
        string $lastName = null,
        string $description = null,
        string $content = null,
        bool $published = null,
        string $job = null,
        bool $executiveOfficer = null,
        bool $deputyGeneralDelegate = false,
        bool $president = false
    ) {
        parent::__construct($uuid, $firstName, $lastName, $description, $content, $published);

        $this->job = $job;
        $this->executiveOfficer = $executiveOfficer;
        $this->deputyGeneralDelegate = $deputyGeneralDelegate;
        $this->president = $president;
    }

    public function setExecutiveOfficer(bool $executiveOfficer): void
    {
        $this->executiveOfficer = $executiveOfficer;
    }

    public function isExecutiveOfficer(): ?bool
    {
        return $this->executiveOfficer;
    }

    public function setDeputyGeneralDelegate(bool $deputyGeneralDelegate): void
    {
        $this->deputyGeneralDelegate = $deputyGeneralDelegate;
    }

    public function isDeputyGeneralDelegate(): ?bool
    {
        return $this->deputyGeneralDelegate;
    }

    public function setPresident(bool $president): void
    {
        $this->president = $president;
    }

    public function isPresident(): ?bool
    {
        return $this->president;
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
}
