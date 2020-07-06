<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use App\AdherentMessage\StaticSegmentInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Exception\CommitteeAlreadyApprovedException;
use App\Report\ReportType;
use App\ValueObject\Link;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * This entity represents a committee group.
 *
 * @ApiResource(
 *     collectionOperations={
 *         "get_my_committees": {
 *             "method": "GET",
 *             "path": "/committees/me",
 *             "access_control": "is_granted('ROLE_ADHERENT')",
 *             "controller": "App\Controller\Api\CommitteesController::myCommitteesAction",
 *             "normalization_context": {
 *                 "groups": {"my_committees"},
 *             },
 *             "pagination_enabled": false,
 *             "swagger_context": {
 *                 "summary": "Retrieves the committees of the current Adherent.",
 *                 "description": "Retrieves the committees of the current Adherent ordered by privilege.",
 *                 "responses": {
 *                     "200": {
 *                         "description": "Committee collection response",
 *                         "schema": {
 *                             "type": "array",
 *                             "items": {
 *                                 "$ref": "#/definitions/Committee-my_committees"
 *                             }
 *                         }
 *                     },
 *                     "401": {
 *                         "description": "Unauthorized if the user is not connected."
 *                     }
 *                 }
 *             }
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "normalization_context": {"groups": {"idea_list_read"}},
 *             "method": "GET",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "swagger_context": {
 *                 "summary": "Retrieves a Committee resource by UUID.",
 *                 "description": "Retrieves a Committee resource by UUID.",
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Committee resource.",
 *                         "example": "515a56c0-bde8-56ef-b90c-4745b1c93818",
 *                     }
 *                 }
 *             }
 *         }
 *     },
 * )
 *
 * @ORM\Table(
 *     name="committees",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="committee_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="committee_canonical_name_unique", columns="canonical_name"),
 *         @ORM\UniqueConstraint(name="committee_slug_unique", columns="slug")
 *     },
 *     indexes={
 *         @ORM\Index(name="committee_status_idx", columns="status")
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Committee extends BaseGroup implements SynchronizedEntity, ReferentTaggableEntity, StaticSegmentInterface
{
    use EntityPostAddressTrait;
    use EntityReferentTagTrait;

    public const STATUSES_NOT_ALLOWED_TO_CREATE_ANOTHER = [
        self::PRE_REFUSED,
        self::PRE_APPROVED,
        self::PENDING,
        self::REFUSED,
    ];

    public const WAITING_STATUSES = [
        self::PENDING,
        self::PRE_APPROVED,
        self::PRE_REFUSED,
    ];

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $coordinatorComment;

    /**
     * The group description.
     *
     * @ORM\Column(type="text")
     *
     * @Algolia\Attribute
     */
    protected $description;

    /**
     * The committee Facebook page URL.
     *
     * @ORM\Column(nullable=true)
     */
    private $facebookPageUrl;

    /**
     * The committee Twitter nickname.
     *
     * @ORM\Column(nullable=true)
     */
    private $twitterNickname;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $adminComment;

    /**
     * @var CitizenProjectCommitteeSupport|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CitizenProjectCommitteeSupport", mappedBy="committee")
     */
    private $citizenProjectSupports;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $nameLocked = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $photoUploaded = false;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mailchimpId;

    /**
     * @var CommitteeElection[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeElection", mappedBy="committee", cascade={"all"}, orphanRemoval=true)
     */
    private $committeeElections;

    /**
     * @var Designation|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Designation\Designation")
     */
    private $currentDesignation;

    /**
     * A cached list of the hosts (for admin).
     */
    public $hosts = [];

    public function __construct(
        UuidInterface $uuid,
        UuidInterface $creator,
        string $name,
        string $description,
        PostAddress $address,
        PhoneNumber $phone = null,
        string $slug = null,
        string $status = self::PENDING,
        string $approvedAt = null,
        string $createdAt = 'now',
        int $membersCount = 0,
        array $citizenProjects = [],
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

        $this->description = $description;
        $this->postAddress = $address;
        $this->citizenProjectSupports = new ArrayCollection();
        $this->referentTags = new ArrayCollection();
        $this->committeeElections = new ArrayCollection();

        foreach ($citizenProjects as $citizenProject) {
            $this->addSupportOnCitizenProject($citizenProject);
        }

        foreach ($referentTags as $referentTag) {
            $this->addReferentTag($referentTag);
        }
    }

    /**
     * @return string
     */
    public function getCoordinatorComment(): ?string
    {
        return $this->coordinatorComment;
    }

    /**
     * @param string $coordinatorComment
     */
    public function setCoordinatorComment(?string $coordinatorComment): void
    {
        $this->coordinatorComment = $coordinatorComment;
    }

    public function getPostAddress(): PostAddress
    {
        return $this->postAddress;
    }

    public function getPhotoPath(): string
    {
        return sprintf('images/committees/%s.jpg', $this->getUuid());
    }

    public function hasPhotoUploaded(): bool
    {
        return $this->photoUploaded;
    }

    public function setPhotoUploaded(bool $photoUploaded): void
    {
        $this->photoUploaded = $photoUploaded;
    }

    public function getCommitteeElection(): ?CommitteeElection
    {
        if (!$this->currentDesignation) {
            return null;
        }

        foreach ($this->committeeElections as $election) {
            if ($election->getDesignation() === $this->currentDesignation) {
                return $election;
            }
        }

        return null;
    }

    public function setCurrentDesignation(Designation $designation): void
    {
        $this->currentDesignation = $designation;
    }

    public function addCommitteeElection(CommitteeElection $committeeElection): void
    {
        if (!$this->committeeElections->contains($committeeElection)) {
            $committeeElection->setCommittee($this);
            $this->committeeElections->add($committeeElection);
        }
    }

    public static function createSimple(
        UuidInterface $uuid,
        string $creatorUuid,
        string $name,
        string $description,
        PostAddress $address,
        PhoneNumber $phone,
        string $createdAt = 'now'
    ): self {
        $committee = new self(
            $uuid,
            Uuid::fromString($creatorUuid),
            $name,
            $description,
            $address,
            $phone
        );
        $committee->createdAt = new \DateTime($createdAt);

        return $committee;
    }

    public static function createForAdherent(
        Adherent $adherent,
        string $name,
        string $description,
        PostAddress $address,
        PhoneNumber $phone,
        string $createdAt = 'now'
    ): self {
        $committee = new self(
            self::createUuid($name),
            clone $adherent->getUuid(),
            $name,
            $description,
            $address,
            $phone
        );
        $committee->createdAt = new \DateTime($createdAt);

        return $committee;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getFacebookPageUrl(): ?string
    {
        return $this->facebookPageUrl;
    }

    public function getTwitterNickname(): ?string
    {
        return $this->twitterNickname;
    }

    public function getAdminComment(): ?string
    {
        return $this->adminComment;
    }

    public function setAdminComment(string $adminComment): void
    {
        $this->adminComment = $adminComment;
    }

    public function isNameLocked(): bool
    {
        return $this->nameLocked;
    }

    public function setNameLocked(bool $nameLocked): void
    {
        $this->nameLocked = $nameLocked;
    }

    public function isWaitingForApproval(): bool
    {
        return \in_array($this->status, self::WAITING_STATUSES, true) && !$this->approvedAt;
    }

    /**
     * Marks this committee as approved.
     */
    public function approved(string $timestamp = 'now'): void
    {
        if ($this->isApproved()) {
            throw new CommitteeAlreadyApprovedException($this->uuid);
        }

        $this->status = self::APPROVED;
        $this->approvedAt = new \DateTime($timestamp);
        $this->refusedAt = null;
    }

    public function setSocialNetworks(string $facebookPageUrl = null, string $twitterNickname = null)
    {
        $this->facebookPageUrl = $facebookPageUrl;
        $this->setTwitterNickname($twitterNickname);
    }

    public function setFacebookPageUrl($facebookPageUrl)
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function setTwitterNickname($twitterNickname)
    {
        $this->twitterNickname = ltrim((string) $twitterNickname, '@');
    }

    /**
     * Returns the list of social networks links.
     *
     * @return Link[]
     */
    public function getSocialNetworksLinks(): array
    {
        $links = [];

        if ($this->facebookPageUrl) {
            $links['facebook'] = $this->createLink($this->facebookPageUrl, 'Facebook');
        }

        if ($this->twitterNickname) {
            $links['twitter'] = $this->createLink(sprintf('https://twitter.com/%s', $this->twitterNickname), 'Twitter');
        }

        return $links;
    }

    public function update(string $name, string $description, PostAddress $address, PhoneNumber $phone): void
    {
        $this->setName($name);
        $this->description = $description;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }

        if (null === $this->phone || !$this->phone->equals($phone)) {
            $this->phone = $phone;
        }
    }

    private function createLink(string $url, string $label): Link
    {
        return new Link($url, $label);
    }

    public function getCitizenProjectSupports(): Collection
    {
        return $this->citizenProjectSupports;
    }

    public function setSupportOnCitizenProjects(iterable $citizenProjects): void
    {
        foreach ($citizenProjects as $citizenProject) {
            $this->addSupportOnCitizenProject($citizenProject);
        }
    }

    public function addSupportOnCitizenProject(CitizenProject $citizenProject): void
    {
        foreach ($this->citizenProjectSupports as $citizenProjectSupport) {
            if ($citizenProject === $citizenProjectSupport->getCitizenProject()) {
                return;
            }
        }

        $this->citizenProjectSupports->add(new CitizenProjectCommitteeSupport($citizenProject, $this, CitizenProjectCommitteeSupport::APPROVED, 'now', 'now'));
    }

    public function removeSupportOnCitizenProject(CitizenProject $citizenProject): void
    {
        foreach ($this->citizenProjectSupports as $citizenProjectSupport) {
            if ($citizenProject === $citizenProjectSupport->getCitizenProject()) {
                $this->citizenProjectSupports->removeElement($citizenProjectSupport);

                return;
            }
        }
    }

    public function getReportType(): string
    {
        return ReportType::COMMITTEE;
    }

    public function getMailchimpId(): ?int
    {
        return $this->mailchimpId;
    }

    public function setMailchimpId(int $mailchimpId): void
    {
        $this->mailchimpId = $mailchimpId;
    }

    public function hasActiveElection(): bool
    {
        return $this->currentDesignation && $this->currentDesignation->isActive();
    }
}
