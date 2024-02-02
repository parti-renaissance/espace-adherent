<?php

namespace App\Entity\GeneralMeeting;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityTimestampableTrait;
use App\Validator\Scope\ScopeVisibility;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     routePrefix="/v3",
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "normalization_context": {
 *             "groups": {"general_meeting_report_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"general_meeting_report_write"},
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'general_meeting_reports')"
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/general_meeting_reports",
 *             "normalization_context": {
 *                 "groups": {"general_meeting_report_list_read"}
 *             },
 *             "maximum_items_per_page": 1000
 *         },
 *         "post": {
 *             "path": "/general_meeting_reports",
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/general_meeting_reports/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'general_meeting_reports') and is_granted('SCOPE_CAN_MANAGE', object)"
 *         },
 *         "put": {
 *             "path": "/general_meeting_reports/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'general_meeting_reports') and is_granted('SCOPE_CAN_MANAGE', object)"
 *         },
 *         "post_file": {
 *             "path": "/general_meeting_reports/{uuid}/file",
 *             "method": "POST",
 *             "controller": "App\Controller\Api\GeneralMeetingReportUploadFileController",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'general_meeting_reports') and is_granted('SCOPE_CAN_MANAGE', object)",
 *         },
 *         "get_file": {
 *             "path": "/general_meeting_reports/{uuid}/file",
 *             "method": "GET",
 *             "controller": "App\Controller\Api\GeneralMeetingReportDownloadFileController",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('RENAISSANCE_ADHERENT') or (is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'general_meeting_reports') and is_granted('SCOPE_CAN_MANAGE', object))",
 *         }
 *     }
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "title": "partial",
 *     "visibility": "exact",
 * })
 *
 * @ApiFilter(ScopeVisibilityFilter::class)
 *
 * @ORM\Entity(repositoryClass="App\Repository\GeneralMeeting\GeneralMeetingReportRepository");
 * @ORM\Table(name="general_meeting_report")
 *
 * @UniqueEntity(fields={"zone", "title"}, message="general_meeting_report.zone_title.unique_entity")
 *
 * @ScopeVisibility
 */
class GeneralMeetingReport implements EntityScopeVisibilityWithZoneInterface, EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;
    use EntityScopeVisibilityTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="Veuillez renseigner un titre.")
     * @Assert\Length(allowEmptyString=true, min=2, minMessage="Le titre doit faire au moins 2 caractères.")
     *
     * @Groups({
     *     "general_meeting_report_list_read",
     *     "general_meeting_report_read",
     *     "general_meeting_report_write",
     * })
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(allowEmptyString=true, min=2, minMessage="La description doit faire au moins 2 caractères.")
     *
     * @Groups({
     *     "general_meeting_report_list_read",
     *     "general_meeting_report_read",
     *     "general_meeting_report_write",
     * })
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     * @Assert\LessThanOrEqual("now")
     *
     * @Groups({
     *     "general_meeting_report_list_read",
     *     "general_meeting_report_read",
     *     "general_meeting_report_write",
     * })
     */
    private ?\DateTime $date = null;

    /**
     * @Assert\File(
     *     maxSize="5M",
     *     binaryFormat=false,
     *     mimeTypes={
     *         "image/*",
     *         "video/mpeg",
     *         "video/mp4",
     *         "video/quicktime",
     *         "video/webm",
     *         "application/pdf",
     *         "application/x-pdf",
     *         "application/vnd.ms-powerpoint",
     *         "application/vnd.openxmlformats-officedocument.presentationml.presentation",
     *         "application/msword",
     *         "application/vnd.ms-excel",
     *         "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *         "application/rtf",
     *         "text/plain",
     *         "text/csv",
     *         "text/html",
     *         "text/calendar"
     *     }
     * )
     */
    private ?UploadedFile $file = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $filePath = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString()
    {
        return (string) $this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function hasFilePath(): bool
    {
        return null !== $this->filePath;
    }
}
