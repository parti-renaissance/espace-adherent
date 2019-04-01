<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Address\GeoCoder;
use AppBundle\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InstitutionalEventRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class InstitutionalEvent extends BaseEvent implements AuthoredInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\InstitutionalEventCategory")
     */
    protected $category;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $invitations;

    public function getType(): ?string
    {
        return $this->type ?? self::INSTITUTIONAL_EVENT_TYPE;
    }

    public function getReportType(): string
    {
        return ReportType::COMMUNITY_EVENT;
    }

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $organizer,
        string $name,
        InstitutionalEventCategory $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt,
        array $invitations = [],
        string $createdAt = null,
        string $slug = null,
        array $referentTags = [],
        string $timeZone = GeoCoder::DEFAULT_TIME_ZONE
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->setName($name);
        $this->slug = $slug;
        $this->category = $category;
        $this->description = $description;
        $this->postAddress = $address;
        $this->invitations = $invitations;
        $this->participantsCount = 0;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->createdAt = new \DateTime($createdAt ?: 'now');
        $this->updatedAt = new \DateTime($createdAt ?: 'now');
        $this->status = self::STATUS_SCHEDULED;
        $this->referentTags = new ArrayCollection($referentTags);
        $this->timeZone = $timeZone;
        $this->invitations = $invitations;
    }

    public function update(
        string $name,
        InstitutionalEventCategory $category,
        string $description,
        PostAddress $address,
        array $invitations,
        \DateTimeInterface $beginAt,
        \DateTimeInterface $finishAt,
        string $timeZone
    ) {
        $this->setName($name);
        $this->category = $category;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->description = $description;
        $this->timeZone = $timeZone;
        $this->invitations = $invitations;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    public function getCategory(): InstitutionalEventCategory
    {
        return $this->category;
    }

    public function isReferentEvent(): bool
    {
        return true;
    }

    public function getInvitations(): array
    {
        return $this->invitations;
    }

    public function setInvitations(array $invitations): void
    {
        $this->invitations = $invitations;
    }

    public function getInvitationsCount(): int
    {
        return \count($this->invitations);
    }

    public function getInvitationsAsString(): string
    {
        return implode(', ', $this->invitations);
    }

    public function getAuthor(): ?Adherent
    {
        return $this->organizer;
    }
}
