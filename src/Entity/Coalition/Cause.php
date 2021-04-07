<?php

namespace App\Entity\Coalition;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\AuthoredTrait;
use App\Entity\AuthorInterface;
use App\Entity\EntityFollowersTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNameSlugTrait;
use App\Entity\ExposedImageOwnerInterface;
use App\Entity\FollowedInterface;
use App\Entity\FollowerInterface;
use App\Entity\ImageTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_client_items_per_page": true,
 *         "order": {"name": "ASC"},
 *         "normalization_context": {
 *             "groups": {"cause_read", "image_owner_exposed"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"cause_write"}
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/causes",
 *         },
 *         "post": {
 *             "path": "/v3/causes",
 *             "access_control": "is_granted('IS_AUTHENTICATED_FULLY')",
 *             "normalization_context": {"groups": {"cause_read"}}
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/causes/{id}",
 *             "requirements": {"id": "%pattern_uuid%"}
 *         },
 *         "follow": {
 *             "method": "PUT|DELETE",
 *             "denormalization_context": {"api_allow_update": false},
 *             "path": "/v3/causes/{id}/follower",
 *             "controller": "App\Controller\Api\FollowController::follower",
 *             "requirements": {"id": "%pattern_uuid%"}
 *         },
 *         "update_image": {
 *             "method": "POST",
 *             "path": "/v3/causes/{uuid}/image",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "defaults": {"_api_receive": false},
 *             "controller": "App\Controller\Api\Coalition\CauseController::updateImage",
 *         },
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"coalition.uuid": "exact"})
 *
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="cause_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="cause_name_unique", columns="name")
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\Coalition\CauseRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="author",
 *         joinColumns={
 *             @ORM\JoinColumn(onDelete="SET NULL")
 *         }
 *     )
 * })
 *
 * @UniqueEntity(fields={"name"})
 *
 * @Assert\Expression("this.getSecondCoalition() === null || this.getSecondCoalition() !== this.getCoalition()", message="Veuillez choisir une autre coalition en tant que secondaire.")
 */
class Cause implements ExposedImageOwnerInterface, AuthoredInterface, FollowedInterface, AuthorInterface
{
    use EntityNameSlugTrait;
    use EntityIdentityTrait;
    use TimestampableEntity;
    use ImageTrait;
    use AuthoredTrait;
    use EntityFollowersTrait;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REFUSED = 'refused';
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REFUSED,
    ];

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
     * @SymfonySerializer\Groups({"cause_read", "cause_write"})
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @SymfonySerializer\Groups({"cause_read", "cause_write"})
     */
    private $description;

    /**
     * @var Collection|CauseFollower[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Coalition\CauseFollower", mappedBy="cause", fetch="EXTRA_LAZY", cascade={"all"})
     */
    private $followers;

    /**
     * @var Coalition|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition", inversedBy="causes")
     * @ORM\JoinColumn(nullable=false)
     *
     * @SymfonySerializer\Groups({"cause_read", "cause_write"})
     *
     * @Assert\NotBlank
     */
    private $coalition;

    /**
     * @var Coalition|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition")
     *
     * @SymfonySerializer\Groups({"cause_read", "cause_write"})
     */
    private $secondCoalition;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = self::STATUS_PENDING;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->followers = new ArrayCollection();
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

    public function getSecondCoalition(): ?Coalition
    {
        return $this->secondCoalition;
    }

    public function setSecondCoalition(?Coalition $secondCoalition): void
    {
        $this->secondCoalition = $secondCoalition;
    }

    public function createFollower(Adherent $adherent): FollowerInterface
    {
        return new CauseFollower($this, $adherent);
    }

    public function setAuthor(Adherent $adherent): void
    {
        $this->author = $adherent;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
    }

    public function refuse(): void
    {
        $this->status = self::STATUS_REFUSED;
    }

    public function isApproved(): bool
    {
        return self::STATUS_APPROVED === $this->status;
    }

    public function isRefused(): bool
    {
        return self::STATUS_REFUSED === $this->status;
    }
}
