<?php

namespace App\Entity\Coalition;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\AuthoredTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\ExposedImageOwnerInterface;
use App\Entity\ImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_client_items_per_page": true,
 *         "order": {"name": "ASC"}
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/causes",
 *             "normalization_context": {
 *                 "groups": {"cause_read", "image_owner_exposed"}
 *             },
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/causes/{id}",
 *             "normalization_context": {"groups": {"cause_read", "image_owner_exposed"}},
 *             "requirements": {"id": "%pattern_uuid%"}
 *         }
 *     },
 * )
 *
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="cause_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="cause_name_unique", columns="name")
 *     }
 * )
 * @ORM\Entity
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="author",
 *         joinColumns={
 *             @ORM\JoinColumn(onDelete="SET NULL")
 *         }
 *     )
 * })
 */
class Cause implements ExposedImageOwnerInterface, AuthoredInterface
{
    use EntityIdentityTrait;
    use TimestampableEntity;
    use ImageTrait;
    use AuthoredTrait;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize="5M",
     *     mimeTypes={"image/jpeg", "image/png"}
     * )
     */
    protected $image;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"cause_read"})
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @SymfonySerializer\Groups({"cause_read"})
     */
    private $description;

    /**
     * @var Coalition|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition", inversedBy="causes")
     * @ORM\JoinColumn(nullable=false)
     *
     * @SymfonySerializer\Groups({"cause_read"})
     */
    private $coalition;

    public function __construct(
        UuidInterface $uuid = null,
        string $name = null,
        string $description = null,
        Coalition $coalition = null,
        Adherent $author = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;
        $this->description = $description;
        $this->coalition = $coalition;
        $this->author = $author;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getImagePath(): string
    {
        return $this->imageName ? \sprintf('images/causes/%s', $this->getImageName()) : '';
    }

    public function getCoalition(): ?Coalition
    {
        return $this->coalition;
    }

    public function setCoalition(Coalition $coalition): void
    {
        $this->coalition = $coalition;
    }
}
