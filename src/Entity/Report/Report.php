<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthoredTrait;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\Report\IdeasWorkshop\IdeaReport;
use AppBundle\Entity\Report\IdeasWorkshop\ThreadReport;
use AppBundle\Entity\Report\IdeasWorkshop\ThreadCommentReport;
use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReportRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "citizen_project": "CitizenProjectReport",
 *     "citizen_action": "CitizenActionReport",
 *     "committee": "CommitteeReport",
 *     "community_event": "CommunityEventReport",
 *     "ideas_workshop_idea": IdeaReport::class,
 *     "ideas_workshop_thread": ThreadReport::class,
 *     "ideas_workshop_thread_comment": ThreadCommentReport::class
 * })
 *
 * @ORM\Table(
 *     name="reports",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="report_uuid_unique", columns="uuid"),
 *     },
 *     indexes={
 *         @ORM\Index(name="report_status_idx", columns="status"),
 *         @ORM\Index(name="report_type_idx", columns="type")
 *     }
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class Report
{
    use EntityIdentityTrait;
    use AuthoredTrait;

    /*
     * Mapping to be defined in concrete classes.
     */
    protected $subject;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    private $reasons;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(length=16, options={"default": AppBundle\Entity\Report\ReportStatusEnum::STATUS_UNRESOLVED})
     */
    private $status = ReportStatusEnum::STATUS_UNRESOLVED;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resolvedAt;

    /**
     * @throws \InvalidArgumentException
     */
    final public function __construct(ReportableInterface $subject, Adherent $author, array $reasons, ?string $comment)
    {
        if (!\count($reasons)) {
            throw new \InvalidArgumentException('At least one reason must be provided');
        }

        if ($invalid = array_diff($reasons, ReportReasonEnum::REASONS_LIST)) {
            throw new \InvalidArgumentException(
                sprintf('Some reasons are not valid "%s", they are defined in %s::REASONS_LIST', implode(', ', $invalid), ReportReasonEnum::class)
            );
        }

        $isOtherReasonChecked = \in_array(ReportReasonEnum::REASON_OTHER, $reasons, true);

        if ($comment && !$isOtherReasonChecked) {
            throw new \InvalidArgumentException(
                sprintf('$comment is filed but %s::REASON_OTHER is not provided in $reasons', ReportReasonEnum::class)
            );
        }

        $this->uuid = Uuid::uuid4();
        $this->subject = $subject;
        $this->author = $author;
        $this->reasons = $reasons;
        $this->comment = $comment;
        $this->createdAt = new \DateTimeImmutable();
    }

    final public function __toString(): string
    {
        return sprintf('Signalement #%d (%s)', $this->id, $this->subject->getReportType());
    }

    final public function getSubject(): ReportableInterface
    {
        return $this->subject;
    }

    final public function getReasons(): array
    {
        return $this->reasons;
    }

    final public function getComment(): ?string
    {
        return $this->comment;
    }

    final public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @throws \LogicException if report already approved
     */
    final public function resolve(): void
    {
        if ($this->isResolved()) {
            throw new \LogicException('Report already resolved');
        }

        $this->status = ReportStatusEnum::STATUS_RESOLVED;
        $this->resolvedAt = new \DateTimeImmutable();
    }

    final public function isResolved(): bool
    {
        return ReportStatusEnum::STATUS_RESOLVED === $this->status;
    }

    final public function getCreatedAt(): \DateTimeImmutable
    {
        if ($this->createdAt instanceof \DateTime) {
            $this->createdAt = \DateTimeImmutable::createFromMutable($this->createdAt);
        }

        return $this->createdAt;
    }

    final public function getResolvedAt(): ?\DateTimeImmutable
    {
        if ($this->resolvedAt instanceof \DateTime) {
            $this->resolvedAt = \DateTimeImmutable::createFromMutable($this->resolvedAt);
        }

        return $this->resolvedAt;
    }

    /**
     * Returns the discriminator. Useful.
     */
    final public function getType(): string
    {
        return $this->subject->getReportType();
    }
}
