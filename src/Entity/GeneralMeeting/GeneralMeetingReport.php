<?php

declare(strict_types=1);

namespace App\Entity\GeneralMeeting;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Controller\Api\GeneralMeetingReportDownloadFileController;
use App\Controller\Api\GeneralMeetingReportUploadFileController;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityTimestampableTrait;
use App\Repository\GeneralMeeting\GeneralMeetingReportRepository;
use App\Validator\Scope\ScopeVisibility;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['title' => 'partial', 'visibility' => 'exact'])]
#[ApiFilter(filterClass: ScopeVisibilityFilter::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/general_meeting_reports/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'general_meeting_reports') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new Put(
            uriTemplate: '/general_meeting_reports/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'general_meeting_reports') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new Post(
            uriTemplate: '/general_meeting_reports/{uuid}/file',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: GeneralMeetingReportUploadFileController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'general_meeting_reports') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new Get(
            uriTemplate: '/general_meeting_reports/{uuid}/file',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: GeneralMeetingReportDownloadFileController::class,
            security: "is_granted('RENAISSANCE_ADHERENT')"
        ),
        new GetCollection(
            uriTemplate: '/general_meeting_reports',
            paginationMaximumItemsPerPage: 1000,
            normalizationContext: ['groups' => ['general_meeting_report_list_read']]
        ),
        new Post(uriTemplate: '/general_meeting_reports'),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['general_meeting_report_read']],
    denormalizationContext: ['groups' => ['general_meeting_report_write']],
    order: ['createdAt' => 'DESC'],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'general_meeting_reports')"
)]
#[ORM\Entity(repositoryClass: GeneralMeetingReportRepository::class)]
#[ORM\Table(name: 'general_meeting_report')]
#[ScopeVisibility]
#[UniqueEntity(fields: ['zone', 'title'], message: 'general_meeting_report.title.unique_entity')]
class GeneralMeetingReport implements EntityScopeVisibilityWithZoneInterface, EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;
    use EntityScopeVisibilityTrait;

    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Veuillez renseigner un titre.'),
        new Assert\Length(min: 2, minMessage: 'Le titre doit faire au moins 2 caractères.'),
    ])]
    #[Groups(['general_meeting_report_list_read', 'general_meeting_report_read', 'general_meeting_report_write'])]
    #[ORM\Column]
    private ?string $title = null;

    #[Assert\Length(min: 2, minMessage: 'La description doit faire au moins 2 caractères.')]
    #[Groups(['general_meeting_report_list_read', 'general_meeting_report_read', 'general_meeting_report_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Assert\LessThanOrEqual('now')]
    #[Assert\NotBlank]
    #[Groups(['general_meeting_report_list_read', 'general_meeting_report_read', 'general_meeting_report_write'])]
    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $date = null;

    #[Assert\File(maxSize: '5M', binaryFormat: false, mimeTypes: ['image/*', 'video/mpeg', 'video/mp4', 'video/quicktime', 'video/webm', 'application/pdf', 'application/x-pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/rtf', 'text/plain', 'text/csv', 'text/html', 'text/calendar'])]
    private ?UploadedFile $file = null;

    #[ORM\Column(nullable: true)]
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
