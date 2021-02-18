<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Address\AddressInterface;
use App\AdherentMessage\StaticSegmentInterface;
use App\Committee\Exception\CommitteeProvisionalSupervisorException;
use App\Committee\Exception\CommitteeSupervisorException;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use App\Entity\VotingPlatform\Designation\EntityElectionHelperTrait;
use App\Exception\CommitteeAlreadyApprovedException;
use App\Report\ReportType;
use App\ValueObject\Genders;
use App\ValueObject\Link;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
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
 */
class Committee extends BaseGroup implements SynchronizedEntity, ReferentTaggableEntity, StaticSegmentInterface, AddressHolderInterface, ZoneableEntity
{
    use EntityPostAddressTrait;
    use EntityReferentTagTrait;
    use EntityZoneTrait;
    use EntityElectionHelperTrait;
    use StaticSegmentTrait;

    public const CLOSED = 'CLOSED';
    public const WAITING_STATUSES = [
        self::PENDING,
        self::PRE_APPROVED,
        self::PRE_REFUSED,
    ];
    public const BLOCKED_STATUSES = [
        self::CLOSED,
        self::REFUSED,
    ];

    /**
     * The group description.
     *
     * @ORM\Column(type="text")
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
     * @var CitizenProjectCommitteeSupport|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CitizenProjectCommitteeSupport", mappedBy="committee")
     */
    private $citizenProjectSupports;

    /**
     * Is also used to block address modification.
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $nameLocked = false;

    /**
     * @var CommitteeElection[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeElection", mappedBy="committee", cascade={"all"}, orphanRemoval=true)
     */
    private $committeeElections;

    /**
     * @var ProvisionalSupervisor[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ProvisionalSupervisor", mappedBy="committee", cascade={"all"}, orphanRemoval=true)
     */
    private $provisionalSupervisors;

    /**
     * A cached list of the hosts (for admin).
     */
    public $hosts = [];

    /**
     * @var CommitteeAdherentMandate|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AdherentMandate\CommitteeAdherentMandate", mappedBy="committee", fetch="EXTRA_LAZY")
     */
    private $adherentMandates;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $closedAt;

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
        $this->adherentMandates = new ArrayCollection();
        $this->referentTags = new ArrayCollection();
        $this->zones = new ArrayCollection();
        $this->committeeElections = new ArrayCollection();
        $this->provisionalSupervisors = new ArrayCollection();

        foreach ($citizenProjects as $citizenProject) {
            $this->addSupportOnCitizenProject($citizenProject);
        }

        foreach ($referentTags as $referentTag) {
            $this->addReferentTag($referentTag);
        }
    }

    public function getPostAddress(): AddressInterface
    {
        return $this->postAddress;
    }

    public function getCommitteeElection(): ?CommitteeElection
    {
        return $this->getCurrentElection();
    }

    /**
     * @return ElectionEntityInterface[]
     */
    public function getElections(): array
    {
        return $this->committeeElections->toArray();
    }

    public function addElection(ElectionEntityInterface $election): void
    {
        if (!$this->committeeElections->contains($election)) {
            $election->setCommittee($this);
            $this->committeeElections->add($election);
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
        ?PhoneNumber $phone = null,
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

    public function isBlocked(): bool
    {
        return \in_array($this->status, self::BLOCKED_STATUSES, true);
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

    public function setDescription(string $description): void
    {
        $this->description = $description;
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

    public function update(string $name, string $description, PostAddress $address): void
    {
        $this->setName($name);
        $this->description = $description;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
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

    public function getAdherentMandates(): Collection
    {
        return $this->adherentMandates;
    }

    public function setAdherentMandates(Collection $adherentMandates): void
    {
        $this->adherentMandates = $adherentMandates;
    }

    public function addAdherentMandate(CommitteeAdherentMandate $adherentMandate): void
    {
        if (!$this->adherentMandates->contains($adherentMandate)) {
            $this->adherentMandates->add($adherentMandate);
        }
    }

    public function removeAdherentMandate(CommitteeAdherentMandate $adherentMandate): void
    {
        $this->adherentMandates->removeElement($adherentMandate);
    }

    public function hasMaleAdherentMandate(): bool
    {
        return $this->hasAdherentMandateWithGender(Genders::MALE);
    }

    public function hasFemaleAdherentMandate(): bool
    {
        return $this->hasAdherentMandateWithGender(Genders::FEMALE);
    }

    public function hasAdherentMandateWithGender(string $gender): bool
    {
        if (0 === $this->adherentMandates->count()) {
            return false;
        }

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
            ->andWhere(Criteria::expr()->eq('gender', $gender))
        ;

        return $this->adherentMandates->matching($criteria)->count() > 0;
    }

    /**
     * @return CommitteeAdherentMandate[]|Collection
     */
    public function getActiveAdherentMandates(): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
            ->orderBy(['gender' => 'ASC'])
        ;

        return $this->adherentMandates->matching($criteria);
    }

    public function getActiveAdherentMandateAdherentIds(): array
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
            ->orderBy(['gender' => 'ASC'])
        ;

        return $this->adherentMandates
            ->matching($criteria)
            ->map(function (CommitteeAdherentMandate $adherentMandate) {
                return $adherentMandate->getAdherent()->getId();
            })
            ->toArray()
        ;
    }

    /**
     * @return ProvisionalSupervisor[]|Collection
     */
    public function getProvisionalSupervisors(): Collection
    {
        return $this->provisionalSupervisors;
    }

    public function hasProvisionalSupervisor(Adherent $adherent): bool
    {
        $found = $this->provisionalSupervisors->filter(function (ProvisionalSupervisor $ps) use ($adherent) {
            return $ps->getAdherent() === $adherent;
        });

        return $found->count() > 0;
    }

    public function getProvisionalSupervisorByGender(string $gender): ?ProvisionalSupervisor
    {
        $found = $this->provisionalSupervisors->filter(function (ProvisionalSupervisor $ps) use ($gender) {
            return $ps->getAdherent()->getGender() === $gender;
        });

        $count = $found->count();

        if ($count > 1) {
            throw new CommitteeProvisionalSupervisorException(\sprintf('More than one %s provisional supervisor has been found for committee with UUID "%s".', $this->getUuid(), $gender));
        }

        return $count > 0 ? $found->first() : null;
    }

    public function addProvisionalSupervisor(Adherent $adherent): void
    {
        if (!$this->hasProvisionalSupervisor($adherent)) {
            $provisionalSupervisor = new ProvisionalSupervisor($adherent, $this);
            $this->provisionalSupervisors->add($provisionalSupervisor);
        }
    }

    public function removeProvisionalSupervisor(ProvisionalSupervisor $provisionalSupervisor): void
    {
        $this->provisionalSupervisors->removeElement($provisionalSupervisor);
    }

    public function updateProvisionalSupervisor(Adherent $adherent): void
    {
        if ($ps = $this->getProvisionalSupervisorByGender($adherent->getGender())) {
            $this->removeProvisionalSupervisor($ps);
        }

        $this->addProvisionalSupervisor($adherent);
    }

    public function getSupervisorMandate(string $gender, bool $isProvisional = false): ?CommitteeAdherentMandate
    {
        $mandates = $this->findSupervisorMandates($gender, $isProvisional);
        $count = $mandates->count();

        if ($count > 1) {
            throw new CommitteeSupervisorException(\sprintf('More than one %s %s supervisor has been found for committee with UUID "%s".', $gender, $isProvisional ? 'provisional' : '', $this->getUuid()));
        }

        return $count > 0 ? $mandates->first() : null;
    }

    /**
     * @return Adherent[]
     */
    public function getSupervisors(bool $isProvisional = null): array
    {
        return array_map(function (CommitteeAdherentMandate $mandate) {
            return $mandate->getAdherent();
        }, $this->findSupervisorMandates(null, $isProvisional)->toArray());
    }

    /**
     * Marks this committee as closed.
     */
    public function close(): void
    {
        $this->status = self::CLOSED;
        $this->closedAt = new \DateTime();
    }

    public function isClosed(): bool
    {
        return self::CLOSED === $this->status;
    }

    /**
     * @return CommitteeAdherentMandate[]|Collection
     */
    public function findSupervisorMandates(?string $gender = null, bool $isProvisional = null): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('quality', CommitteeMandateQualityEnum::SUPERVISOR))
            ->andWhere(Criteria::expr()->orX(
                Criteria::expr()->isNull('finishAt'),
                Criteria::expr()->gt('finishAt', new \DateTime())
            ))
        ;

        if ($gender) {
            $criteria->andWhere(Criteria::expr()->eq('gender', $gender));
        }

        if (\is_bool($isProvisional)) {
            $criteria->andWhere(Criteria::expr()->eq('provisional', $isProvisional));
        }

        return $this->adherentMandates->matching($criteria);
    }
}
