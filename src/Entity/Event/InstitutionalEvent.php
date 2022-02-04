<?php

namespace App\Entity\Event;

use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\PostAddress;
use App\Entity\Report\ReportableInterface;
use App\Event\EventTypeEnum;
use App\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InstitutionalEventRepository")
 */
class InstitutionalEvent extends BaseEvent implements AuthoredInterface, ReportableInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\InstitutionalEventCategory")
     *
     * @Groups({"event_list_read"})
     */
    protected $category;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $invitations;

    public function getType(): string
    {
        return EventTypeEnum::TYPE_INSTITUTIONAL;
    }

    public function getReportType(): string
    {
        return ReportType::COMMUNITY_EVENT;
    }

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $organizer,
        string $name,
        EventCategoryInterface $category,
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
        parent::__construct($uuid);

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
        $this->referentTags = new ArrayCollection($referentTags);
        $this->zones = new ArrayCollection();
        $this->timeZone = $timeZone;
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
