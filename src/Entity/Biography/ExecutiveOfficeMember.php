<?php

namespace AppBundle\Entity\Biography;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Validator\UniqueExecutiveOfficeMemberRole;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Biography\ExecutiveOfficeMemberRepository")
 * @ORM\Table(
 *     name="biography_executive_office_member",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="executive_office_member_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="executive_office_member_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueExecutiveOfficeMemberRole
 *
 * @Algolia\Index(autoIndex=false)
 */
class ExecutiveOfficeMember extends AbstractBiography
{
    /**
     * @ORM\Column
     *
     * @Assert\Length(max=255)
     */
    private $job;

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
        bool $deputyGeneralDelegate = false
    ) {
        parent::__construct($uuid, $firstName, $lastName, $description, $content, $published);

        $this->job = $job;
        $this->executiveOfficer = $executiveOfficer;
        $this->deputyGeneralDelegate = $deputyGeneralDelegate;
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
