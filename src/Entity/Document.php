<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\Api\DocumentDownloadFileController;
use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['title' => 'partial'])]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/documents/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'documents')"
        ),
        new Get(
            uriTemplate: '/documents/{uuid}/file',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: DocumentDownloadFileController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'documents')"
        ),
        new GetCollection(
            uriTemplate: '/documents',
            paginationMaximumItemsPerPage: 1000
        ),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['document_read']],
    order: ['createdAt' => 'DESC'],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'documents')"
)]
#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table]
#[UniqueEntity(fields: ['title'], message: 'document.title.unique_entity')]
class Document implements \Stringable, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Veuillez renseigner un titre.'),
        new Assert\Length(min: 2, minMessage: 'Le titre doit faire au moins 2 caractères.'),
    ])]
    #[Groups(['document_read'])]
    #[ORM\Column(unique: true)]
    public ?string $title = null;

    #[Assert\Length(min: 2, minMessage: 'La description doit faire au moins 2 caractères.')]
    #[Groups(['document_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $comment = null;

    #[Assert\File(maxSize: '5M', binaryFormat: false, mimeTypes: ['image/*', 'video/mpeg', 'video/mp4', 'video/quicktime', 'video/webm', 'application/pdf', 'application/x-pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/rtf', 'text/plain', 'text/csv', 'text/html', 'text/calendar'])]
    public ?UploadedFile $file = null;

    #[ORM\Column(nullable: true)]
    public ?string $filePath = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString()
    {
        return (string) $this->title;
    }

    public function hasFilePath(): bool
    {
        return null !== $this->filePath;
    }
}
