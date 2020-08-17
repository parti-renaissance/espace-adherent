<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\AdherentMessage\StaticSegmentInterface;
use App\Collection\AdherentCollection;
use App\Exception\CitizenProjectAlreadyApprovedException;
use App\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * This entity represents a citizen project.
 *
 * @ORM\Table(
 *     name="citizen_projects",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="citizen_project_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="citizen_project_slug_unique", columns="slug")
 *     },
 *     indexes={
 *         @ORM\Index(name="citizen_project_status_idx", columns="status")
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CitizenProjectRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProject extends BaseGroup implements SynchronizedEntity, ReferentTaggableEntity, StaticSegmentInterface
{
    use EntityNullablePostAddressTrait;
    use EntityReferentTagTrait;
    use SkillTrait;
    use StaticSegmentTrait;

    public const STATUSES_NOT_ALLOWED_TO_CREATE = [
        self::PENDING,
        self::PRE_APPROVED,
        self::PRE_REFUSED,
    ];

    public const NOT_FINAL_STATUSES = [
        self::PENDING,
        self::PRE_APPROVED,
        self::PRE_REFUSED,
    ];

    public const SIMPLE_TYPE = 'simple';
    public const TURNKEY_TYPE = 'turnkey';

    public const TYPES = [
        self::SIMPLE_TYPE => 'Projet simple',
        self::TURNKEY_TYPE => 'Projet relié à un projet CEM',
    ];

    /**
     * @ORM\Column
     *
     * @Gedmo\Slug(fields={"postAddress.postalCode", "canonicalName"})
     *
     * @Algolia\Attribute
     *
     * @JMS\Groups({"public", "citizen_project_read"})
     */
    protected $slug;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CitizenProjectCategory")
     *
     * @Algolia\Attribute
     */
    private $category;

    /**
     * @ORM\Column
     *
     * @Algolia\Attribute
     *
     * @JMS\Groups({"public", "citizen_project_read"})
     */
    protected $subtitle;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $problemDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $proposedSolution;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $requiredMeans;

    /**
     * @var CitizenProjectCommitteeSupport[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\CitizenProjectCommitteeSupport",
     *     fetch="EAGER",
     *     mappedBy="citizenProject",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    private $committeeSupports;

    /**
     * @var Skill[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\CitizenProjectSkill")
     * @ORM\JoinTable(
     *     name="citizen_projects_skills",
     *     joinColumns={
     *         @ORM\JoinColumn(name="citizen_project_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="citizen_project_skill_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    private $skills;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $matchedSkills = false;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $featured = false;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $adminComment;

    /**
     * @var string
     *
     * @ORM\Column(length=50, nullable=true)
     *
     * @JMS\Groups({"public", "citizen_project_read"})
     */
    private $district;

    /**
     * A cached list of the administrators (for admin).
     *
     * @var AdherentCollection|null
     */
    private $administrators;

    /**
     * @var Adherent|null
     */
    private $creator;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $imageUploaded = false;

    /**
     * @var UploadedFile|null
     */
    private $image;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     */
    private $imageName;

    /**
     * @var TurnkeyProject|null
     *
     * @ORM\ManyToOne(targetEntity="TurnkeyProject")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $turnkeyProject;

    /**
     * @var CitizenAction|null
     */
    private $nextAction;

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $creator,
        string $name,
        string $subtitle,
        CitizenProjectCategory $category,
        array $committees = [],
        string $problemDescription = '',
        string $proposedSolution = '',
        string $requiredMeans = '',
        PhoneNumber $phone = null,
        NullablePostAddress $address = null,
        string $district = null,
        TurnkeyProject $turnkeyProject = null,
        string $slug = null,
        string $status = self::PENDING,
        string $approvedAt = null,
        string $createdAt = 'now',
        int $membersCount = 0,
        array $referentTags = []
    ) {
        parent::__construct(
            $uuid,
            $creator,
            $name,
            $slug,
            $phone,
            $status,
            $approvedAt,
            $createdAt,
            $membersCount
        );

        $this->category = $category;
        $this->subtitle = $subtitle;
        $this->postAddress = $address;
        $this->district = $district;
        $this->turnkeyProject = $turnkeyProject;
        $this->problemDescription = $problemDescription;
        $this->proposedSolution = $proposedSolution;
        $this->requiredMeans = $requiredMeans;
        $this->skills = new ArrayCollection();
        $this->committeeSupports = new ArrayCollection();
        $this->setCommitteesOnSupport($committees);
        $this->referentTags = new ArrayCollection();

        foreach ($referentTags as $referentTag) {
            $this->addReferentTag($referentTag);
        }
    }

    public function getPostAddress(): NullablePostAddress
    {
        return $this->postAddress;
    }

    public function setCategory(CitizenProjectCategory $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): CitizenProjectCategory
    {
        return $this->category;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("category"),
     * @JMS\Groups({"public", "citizen_project_read"})
     */
    public function getCategoryName(): string
    {
        return $this->category->getName();
    }

    public function isSupportedByCommitteeUuid(string $committeeUuid): bool
    {
        foreach ($this->committeeSupports as $committeeSupport) {
            if ($committeeSupport->isApproved() && $committeeSupport->getCommittee()->uuid->toString() === $committeeUuid) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return CitizenProjectCommitteeSupport[]|Collection
     */
    public function getCommitteeSupports(): Collection
    {
        return $this->committeeSupports;
    }

    public function getApprovedCommitteeSupports(): Collection
    {
        return $this->committeeSupports->filter(function (CitizenProjectCommitteeSupport $c) {
            return $c->isApproved();
        });
    }

    /**
     * @return CitizenProjectCommitteeSupport[]|Collection
     */
    public function getPendingCommitteeSupports(): Collection
    {
        return $this->committeeSupports->filter(function (CitizenProjectCommitteeSupport $c) {
            return $c->isPending();
        });
    }

    public function setCommitteeSupports(iterable $committeeSupports): void
    {
        $this->committeeSupports = new ArrayCollection();
        foreach ($committeeSupports as $committeeSupport) {
            $this->addCommitteeSupport($committeeSupport);
        }
    }

    public function addCommitteeSupport(CitizenProjectCommitteeSupport $committeeSupport): void
    {
        if (!$this->committeeSupports->contains($committeeSupport)) {
            $this->committeeSupports->add($committeeSupport);
        }
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
            if ($committee->getId() === $committeeSupport->getCommittee()->getId()) {
                return;
            }
        }

        $this->addCommitteeSupport(new CitizenProjectCommitteeSupport($this, $committee));
    }

    public function removeCommitteeSupport(Committee $committee): void
    {
        foreach ($this->committeeSupports as $committeeSupport) {
            if ($committee->getId() === $committeeSupport->getCommittee()->getId()) {
                $this->committeeSupports->removeElement($committeeSupport);

                return;
            }
        }
    }

    public function setSubtitle(string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
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

    public function getImagePath(): string
    {
        return sprintf('images/citizen_projects/%s', $this->getImageName());
    }

    public function getAssetImagePath(): string
    {
        return sprintf('%s/%s', 'assets', $this->getImagePath());
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    public function setImage(?UploadedFile $image): void
    {
        $this->image = $image;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function setImageNameFromUploadedFile(?UploadedFile $image): void
    {
        $this->imageName = null === $image ? null :
            sprintf('%s.%s',
                md5(sprintf('%s@%s', $this->getUuid(), $image->getClientOriginalName())),
                $image->getClientOriginalExtension()
            )
        ;
    }

    public function setImageNameFromTurnkeyProject(TurnkeyProject $turnkeyProject): void
    {
        $imageName = $turnkeyProject->getImageName();
        $this->imageName = sprintf('%s.%s',
                md5(sprintf('%s@%s', $this->getUuid(), $imageName)),
                substr(strrchr($imageName, '.'), 1)
            )
        ;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function hasImageUploaded(): bool
    {
        return $this->imageUploaded;
    }

    public function setImageUploaded(bool $imageUploaded): void
    {
        $this->imageUploaded = $imageUploaded;
    }

    public static function createForAdherent(
        Adherent $adherent,
        string $name,
        string $subtitle,
        CitizenProjectCategory $category,
        PhoneNumber $phone,
        string $problemDescription,
        string $proposedSolution,
        string $requiredMeans,
        array $committees = [],
        NullablePostAddress $address = null,
        string $district = null,
        TurnkeyProject $turnkeyProject = null,
        string $createdAt = 'now'
    ): self {
        $citizenProject = new self(
            Uuid::uuid4(),
            clone $adherent->getUuid(),
            $name,
            $subtitle,
            $category,
            $committees,
            $problemDescription,
            $proposedSolution,
            $requiredMeans,
            $phone,
            $address,
            $district,
            $turnkeyProject
        );

        $citizenProject->createdAt = new \DateTime($createdAt);
        $citizenProject->status = self::PENDING;

        return $citizenProject;
    }

    /**
     * Marks this citizen project as approved.
     *
     * @throws \App\Exception\CitizenProjectAlreadyApprovedException
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
        string $problemDescription,
        string $proposedSolution,
        string $requiredMeans,
        NullablePostAddress $address,
        PhoneNumber $phone,
        iterable $skills,
        iterable $committees,
        ?UploadedFile $image,
        ?string $district
    ): void {
        $this->setName($name);
        $this->setSubtitle($subtitle);
        $this->setCategory($category);
        $this->setProblemDescription($problemDescription);
        $this->setProposedSolution($proposedSolution);
        $this->setRequiredMeans($requiredMeans);
        $this->setSkills($skills);
        $this->setDistrict($district);

        if ($image instanceof UploadedFile) {
            $this->setImage($image);
        }

        if (null === $this->postAddress || !$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }

        if (null === $this->phone || !$this->phone->equals($phone)) {
            $this->phone = $phone;
        }

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
            if (\in_array($committeeSupport->getCommittee()->getId(), $committeeIdsToBeDissociate)) {
                $this->removeCommitteeSupport($committeeSupport->getCommittee());
            }
        }

        foreach ($committees as $committee) {
            if (\in_array($committee->getId(), $committeeIdsToBeAssociate)) {
                $this->addCommitteeOnSupport($committee);
            }
        }
    }

    public function setCreator(?Adherent $creator): void
    {
        $this->creator = $creator;
    }

    public function getCreator(): ?Adherent
    {
        return $this->creator;
    }

    /**
     * @return CitizenProjectSkill[]|Collection
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function setSkills(iterable $skills): void
    {
        $this->skills = new ArrayCollection();
        foreach ($skills as $skill) {
            if (!$skill instanceof CitizenProjectSkill) {
                throw new \InvalidArgumentException('Invalid argument type, require CitizenProjectSkill');
            }
            $this->addSkill($skill);
        }
    }

    public function addSkill(CitizenProjectSkill $citizenProjectSkill): void
    {
        if (!$this->skills->contains($citizenProjectSkill)) {
            $this->skills->add($citizenProjectSkill);
        }
    }

    public function removeSkill(CitizenProjectSkill $citizenProjectSkill): void
    {
        $this->skills->removeElement($citizenProjectSkill);
    }

    /**
     * @return AdherentCollection
     */
    public function getAdministrators(): ?AdherentCollection
    {
        return $this->administrators;
    }

    public function setAdministrators(AdherentCollection $administrators): void
    {
        $this->administrators = $administrators;
    }

    public function getMatchedSkills(): bool
    {
        return $this->matchedSkills;
    }

    public function setMatchedSkills(bool $matchedSkills): void
    {
        $this->matchedSkills = $matchedSkills;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): void
    {
        $this->featured = $featured;
    }

    public function getAdminComment(): ?string
    {
        return $this->adminComment;
    }

    public function setAdminComment(?string $adminComment): void
    {
        $this->adminComment = $adminComment;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(?string $district): void
    {
        $this->district = $district;
    }

    public function getTurnkeyProject(): ?TurnkeyProject
    {
        return $this->turnkeyProject;
    }

    public function setTurnkeyProject(TurnkeyProject $turnkeyProject): void
    {
        $this->turnkeyProject = $turnkeyProject;
    }

    public function isFromTurnkeyProject(): bool
    {
        return $this->turnkeyProject instanceof TurnkeyProject;
    }

    public function getNextAction(): ?CitizenAction
    {
        return $this->nextAction;
    }

    public function setNextAction(CitizenAction $citizenAction): void
    {
        $this->nextAction = $citizenAction;
    }

    public function getReportType(): string
    {
        return ReportType::CITIZEN_PROJECT;
    }

    public function exportCommitteeSupports(): string
    {
        return \implode(', ', array_map(function (CitizenProjectCommitteeSupport $committeeSupport) {
            return \sprintf('%s [%s]', $committeeSupport->getCommittee()->getName(), $committeeSupport->getStatus());
        }, $this->committeeSupports->toArray()));
    }

    public function exportSkills(): string
    {
        return \implode(', ', $this->skills->toArray());
    }

    public function getNameWithDistrict(): string
    {
        return $this->district ? sprintf('%s - %s', $this->name, $this->district) : $this->name;
    }

    public function isNotFinalStatus(): bool
    {
        return \in_array($this->status, self::NOT_FINAL_STATUSES, true);
    }

    public function getProjectType(): string
    {
        if ($this->isFromTurnkeyProject()) {
            return self::TYPES[self::TURNKEY_TYPE];
        }

        return self::TYPES[self::SIMPLE_TYPE];
    }
}
