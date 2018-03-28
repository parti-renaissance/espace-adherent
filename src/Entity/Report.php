<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReportRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"citizen_project" = "CitizenProjectReport"})
 *
 * @ORM\Table(
 *   name="reports",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="report_uuid_unique", columns="uuid"),
 *   },
 *   indexes={
 *     @ORM\Index(name="report_status_idx", columns="status"),
 *     @ORM\Index(name="report_type_idx", columns="type")
 *   }
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class Report
{
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_UNRESOLVED = 'unresolved';

    public const STATUS_LIST = [
        self::STATUS_RESOLVED,
        self::STATUS_UNRESOLVED,
    ];

    public const REASON_EN_MARCHE_VALUES = 'en_marche_values';
    public const REASON_INAPPROPRIATE = 'inappropriate';
    public const REASON_COMMERCIAL_CONTENT = 'commercial_content';
    public const REASON_OTHER = 'other';

    public const REASONS_LIST = [
        self::REASON_EN_MARCHE_VALUES,
        self::REASON_INAPPROPRIATE,
        self::REASON_COMMERCIAL_CONTENT,
        self::REASON_OTHER,
    ];

    use EntityIdentityTrait;
    use AuthoredTrait;

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
     * @ORM\Column(length=16)
     */
    private $status;

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
    public function __construct(Adherent $author, array $reasons, ?string $comment)
    {
        if (!count($reasons)) {
            throw new \InvalidArgumentException('At least one reason must be provided');
        }

        foreach ($reasons as $reason) {
            if (!in_array($reason, self::REASONS_LIST, true)) {
                throw new \InvalidArgumentException(
                    sprintf('%s is not a valid reason, you must choose one from %s::REASONS_LIST', $reason, self::class)
                );
            }
        }

        $isOtherReasonChecked = in_array(self::REASON_OTHER, $reasons, true);

        if ($comment && !$isOtherReasonChecked) {
            throw new \InvalidArgumentException(
                sprintf('$comment is filed but %s::REASON_OTHER is not provided in $reasons', self::class)
            );
        }

        if (!$comment && $isOtherReasonChecked) {
            throw new \InvalidArgumentException(
                sprintf('$comment is not filed while %s::REASON_OTHER is provided', self::class)
            );
        }

        $this->uuid = Uuid::uuid4();
        $this->comment = $comment;
        $this->reasons = $reasons;
        $this->author = $author;
        $this->createdAt = new \DateTimeImmutable();
        $this->status = self::STATUS_UNRESOLVED;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @throws \LogicException if report already approved
     */
    public function resolve(): void
    {
        if ($this->isResolved()) {
            throw new \LogicException('Report already resolved');
        }

        $this->status = self::STATUS_RESOLVED;
        $this->resolvedAt = new \DateTimeImmutable();
    }

    public function isResolved(): bool
    {
        return self::STATUS_RESOLVED === $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        if ($this->createdAt instanceof \DateTime) {
            $this->createdAt = \DateTimeImmutable::createFromMutable($this->createdAt);
        }

        return $this->createdAt;
    }

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        if ($this->resolvedAt instanceof \DateTime) {
            $this->resolvedAt = \DateTimeImmutable::createFromMutable($this->resolvedAt);
        }

        return $this->resolvedAt;
    }

    public function __toString()
    {
        return 'Signalement #'.$this->getId();
    }

    /**
     * Return the subject of the report.
     *
     * @return mixed
     */
    abstract public function getSubject();

    /**
     * Return the type of the subject. Useful.
     *
     * @return mixed
     */
    abstract public function getSubjectType(): string;
}
