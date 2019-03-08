<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Exception\CommitteeAlreadyApprovedException;
use AppBundle\Report\ReportType;
use AppBundle\ValueObject\Link;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
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
 *             "controller": "AppBundle\Controller\Api\CommitteesController::myCommitteesAction",
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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Committee extends BaseGroup implements SynchronizedEntity, ReferentTaggableEntity
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
     * The committee Google+ page URL.
     *
     * @ORM\Column(nullable=true)
     */
    private $googlePlusPageUrl;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $adminComment;

    /**
     * @var CitizenProjectCommitteeSupport|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CitizenProjectCommitteeSupport", mappedBy="committee")
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

    public function getGooglePlusPageUrl(): ?string
    {
        return $this->googlePlusPageUrl;
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

    public function setSocialNetworks(
        string $facebookPageUrl = null,
        string $twitterNickname = null,
        string $googlePlusPageUrl = null
    ) {
        $this->facebookPageUrl = $facebookPageUrl;
        $this->setTwitterNickname($twitterNickname);
        $this->googlePlusPageUrl = $googlePlusPageUrl;
    }

    public function setFacebookPageUrl($facebookPageUrl)
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function setTwitterNickname($twitterNickname)
    {
        $this->twitterNickname = ltrim((string) $twitterNickname, '@');
    }

    public function setGooglePlusPageUrl($googlePlusPageUrl)
    {
        $this->googlePlusPageUrl = $googlePlusPageUrl;
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

        if ($this->googlePlusPageUrl) {
            $links['google_plus'] = $this->createLink($this->googlePlusPageUrl, 'Google +');
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

    /**
     * @return Collection|ReferentTag[]
     */
    public function getReferentTags(): Collection
    {
        return $this->referentTags;
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        if (!$this->referentTags->contains($referentTag)) {
            $this->referentTags->add($referentTag);
        }
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTags->remove($referentTag);
    }

    public function clearReferentTags(): void
    {
        $this->referentTags->clear();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("tags")
     * @JMS\Groups({"public", "committee_read"})
     */
    public function getReferentTagsCodes(): array
    {
        return array_map(function (ReferentTag $referentTag) {
            return $referentTag->getCode();
        }, $this->referentTags->toArray());
    }
}
