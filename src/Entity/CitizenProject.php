<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Exception\CitizenProjectAlreadyApprovedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This entity represents a citizen project.
 *
 * @ORM\Table(
 *   name="citizen_projects",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="citizen_project_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="citizen_project_canonical_name_unique", columns="canonical_name"),
 *     @ORM\UniqueConstraint(name="citizen_project_slug_unique", columns="slug")
 *   },
 *   indexes={
 *     @ORM\Index(name="citizen_project_status_idx", columns="status")
 *   }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenProjectRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProject extends BaseGroup
{
    use EntityNullablePostAddressTrait;
    use SkillTrait;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CitizenProjectCategory")
     *
     * @Algolia\Attribute
     */
    private $category;

    /**
     * @ORM\Column
     *
     * @Algolia\Attribute
     */
    protected $subtitle;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=500)
     */
    private $problemDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=800)
     */
    private $proposedSolution;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=500)
     */
    private $requiredMeans;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $assistanceNeeded = false;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $assistanceContent;

    /**
     * @var CitizenProjectCommitteeSupport[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CitizenProjectCommitteeSupport", mappedBy="citizenProject", orphanRemoval=true,
     *      cascade={"persist"})
     *
     * @Algolia\Attribute
     */
    private $committeeSupports;

    /**
     * @var Skill[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\CitizenProjectSkill")
     * @ORM\JoinTable(
     *     name="citizen_projects_skills",
     *     joinColumns={
     *         @ORM\JoinColumn(name="citizen_project_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="citizen_project_skill_id", referencedColumnName="id")
     *     }
     * )
     */
    private $skills;

    /**
     * A cached list of the administrators (for admin).
     */
    public $administrators = [];

    /**
     * @var Adherent|null
     */
    private $creator;

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $creator,
        string $name,
        string $subtitle,
        CitizenProjectCategory $category,
        array $committees = [],
        bool $assistanceNeeded = false,
        ?string $assistanceContent = null,
        string $problemDescription = '',
        string $proposedSolution = '',
        string $requiredMeans = '',
        PhoneNumber $phone = null,
        NullablePostAddress $address = null,
        string $slug = null,
        string $status = self::PENDING,
        string $approvedAt = null,
        string $createdAt = 'now',
        int $membersCount = 0
    ) {
        if ($approvedAt) {
            $approvedAt = new \DateTimeImmutable($approvedAt);
        }

        if ($createdAt) {
            $createdAt = new \DateTimeImmutable($createdAt);
        }

        $this->uuid = $uuid;
        $this->createdBy = $creator;
        $this->setName($name);
        $this->slug = $slug;
        $this->category = $category;
        $this->subtitle = $subtitle;
        $this->postAddress = $address;
        $this->phone = $phone;
        $this->assistanceNeeded = $assistanceNeeded;
        $this->assistanceContent = $assistanceContent;
        $this->status = $status;
        $this->membersCounts = $membersCount;
        $this->approvedAt = $approvedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
        $this->problemDescription = $problemDescription;
        $this->proposedSolution = $proposedSolution;
        $this->requiredMeans = $requiredMeans;
        $this->skills = new ArrayCollection();
        $this->committeeSupports = new ArrayCollection();

        foreach ($committees as $committee) {
            $this->addCommitteeOnSupport($committee);
        }
    }

    public function getPostAddress(): NullablePostAddress
    {
        return $this->postAddress;
    }

    public function getLatitude()
    {
        return $this->postAddress ? $this->postAddress->getLatitude() : null;
    }

    public function getLongitude()
    {
        return $this->postAddress ? $this->postAddress->getLongitude() : null;
    }

    public function getGeocodableAddress(): string
    {
        return $this->postAddress ? $this->postAddress->getGeocodableAddress() : '';
    }

    public function setCategory(CitizenProjectCategory $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): CitizenProjectCategory
    {
        return $this->category;
    }

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getCommitteeSupports(): Collection
    {
        return $this->committeeSupports;
    }

    public function getCommitteeSupportsApproved(): Collection
    {
        return $this->committeeSupports->filter(function (CitizenProjectCommitteeSupport $c) {
            return $c->isApprove();
        });
    }

    public function getCommitteeSupportsPending(): Collection
    {
        return $this->committeeSupports->filter(function (CitizenProjectCommitteeSupport $c) {
            return $c->isPending();
        });
    }

    public function setCommitteeSupports(Collection $committeeSupports): void
    {
        $this->committeeSupports = $committeeSupports;
    }

    public function setCommitteesOnSupport(iterable $committees): void
    {
        foreach ($committees as $committee) {
            $this->addCommitteeOnSupport($committee);
        }
    }

    public function addCommitteeOnSupport(Committee $committee): void
    {
        foreach ($this->committeeSupports as $committeeSupport) {
            if ($committee === $committeeSupport->getCommittee()) {
                return;
            }
        }

        $this->committeeSupports->add(new CitizenProjectCommitteeSupport($this, $committee));
    }

    public function removeCommitteeSupport(Committee $committee): void
    {
        foreach ($this->committeeSupports as $committeeSupport) {
            if ($committee === $committeeSupport->getCommittee()) {
                $this->committeeSupports->removeElement($committeeSupport);

                return;
            }
        }
    }

    public function setSubtitle(string $subtitle)
    {
        $this->subtitle = $subtitle;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function isAssistanceNeeded(): bool
    {
        return $this->assistanceNeeded;
    }

    /**
     * @return string
     */
    public function getAssistanceContent(): ?string
    {
        return $this->assistanceContent;
    }

    /**
     * @param string $assistanceContent
     */
    public function setAssistanceContent(?string $assistanceContent): void
    {
        $this->assistanceContent = $assistanceContent;
    }

    public function setAssistanceNeeded(bool $assistanceNeeded): void
    {
        $this->assistanceNeeded = $assistanceNeeded;
    }

    public function setProblemDescription(?string $problemDescription): void
    {
        $this->problemDescription = $problemDescription;
    }

    public function getProblemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function setProposedSolution(?string $proposedSolution): void
    {
        $this->proposedSolution = $proposedSolution;
    }

    public function getProposedSolution(): ?string
    {
        return $this->proposedSolution;
    }

    public function setRequiredMeans(?string $requiredMeans): void
    {
        $this->requiredMeans = $requiredMeans;
    }

    public function getRequiredMeans(): ?string
    {
        return $this->requiredMeans;
    }

    public static function createForAdherent(
        Adherent $adherent,
        string $name,
        string $subtitle,
        CitizenProjectCategory $category,
        PhoneNumber $phone,
        bool $assistanceNeeded,
        ?string $assistanceContent,
        string $problemDescription,
        string $proposedSolution,
        string $requiredMeans,
        array $committees = [],
        NullablePostAddress $address = null,
        string $createdAt = 'now'
    ): self {
        $citizenProject = new self(
            self::createUuid($name),
            clone $adherent->getUuid(),
            $name,
            $subtitle,
            $category,
            $committees,
            $assistanceNeeded,
            $assistanceContent,
            $problemDescription,
            $proposedSolution,
            $requiredMeans,
            $phone,
            $address
        );

        $citizenProject->createdAt = new \DateTime($createdAt);
        $citizenProject->status = self::PENDING;

        return $citizenProject;
    }

    /**
     * Marks this citizen project as approved.
     *
     * @param string $timestamp
     *
     * @throws \AppBundle\Exception\CitizenProjectAlreadyApprovedException
     */
    public function approved(string $timestamp = 'now'): void
    {
        if ($this->isApproved()) {
            throw new CitizenProjectAlreadyApprovedException($this->uuid);
        }

        $this->status = self::APPROVED;
        $this->approvedAt = new \DateTime($timestamp);
        $this->refusedAt = null;
    }

    public function update(
        string $name,
        string $subtitle,
        CitizenProjectCategory $category,
        bool $assistanceNeeded,
        string $assistanceContent,
        string $problemDescription,
        string $proposedSolution,
        string $requiredMeans,
        NullablePostAddress $address,
        Collection $committees
    ): void {
        $this->setName($name);
        $this->setSubtitle($subtitle);
        $this->setCategory($category);
        $this->setAssistanceNeeded($assistanceNeeded);
        $this->setAssistanceContent($assistanceContent);
        $this->setProblemDescription($problemDescription);
        $this->setProposedSolution($proposedSolution);
        $this->setRequiredMeans($requiredMeans);

        if (null === $this->postAddress || !$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }

        if ($this->isPending()) {
            $committeeIdsSubmit = [];
            $committeeIdsAlreadySupport = [];
            foreach ($committees as $committee) {
                $committeeIdsSubmit[] = $committee->getId();
            }

            foreach ($this->getCommitteeSupports() as $committeeSupport) {
                $committeeIdsAlreadySupport[] = $committeeSupport->getCommittee()->getId();
            }

            $committeeIdsToBeDissociate = array_diff($committeeIdsAlreadySupport, $committeeIdsSubmit);
            $committeeIdsToBeAssociate = array_diff($committeeIdsSubmit, $committeeIdsAlreadySupport);

            foreach ($this->getCommitteeSupports() as $committeeSupport) {
                if (in_array($committeeSupport->getCommittee()->getId(), $committeeIdsToBeDissociate)) {
                    $this->removeCommitteeSupport($committeeSupport->getCommittee());
                }
            }

            foreach ($committees as $committee) {
                if (in_array($committee->getId(), $committeeIdsToBeAssociate)) {
                    $this->addCommitteeOnSupport($committee);
                }
            }
        }
    }

    public function getCitizenProjectSkills(): array
    {
        // hardcode temporary to test the show view
        return [
            'Toutes les bonnes volontés',
            'Expert web',
            'Professeur de maths',
        ];
    }

    public function setCreator(?Adherent $creator): void
    {
        $this->creator = $creator;
    }

    public function getCreator(): ?Adherent
    {
        return $this->creator;
    }
}
