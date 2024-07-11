<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\DocumentRepository;
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
 *             "groups": {"document_read"}
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'documents')",
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/documents",
 *             "maximum_items_per_page": 1000
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/documents/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'documents')",
 *         },
 *         "get_file": {
 *             "path": "/documents/{uuid}/file",
 *             "method": "GET",
 *             "controller": "App\Controller\Api\DocumentDownloadFileController",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'documents')",
 *         }
 *     }
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "title": "partial",
 * })
 */
#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table]
#[UniqueEntity(fields: ['title'], message: 'document.title.unique_entity')]
class Document implements EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\Length(min: 2, minMessage: 'Le titre doit faire au moins 2 caractères.', options: ['allowEmptyString' => true])]
    #[Assert\NotBlank(message: 'Veuillez renseigner un titre.')]
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
