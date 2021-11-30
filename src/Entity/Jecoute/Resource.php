<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\ExposedImageOwnerInterface;
use App\Entity\ImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order": {"position": "ASC"},
 *         "normalization_context": {"groups": {"jecoute_resources_read", "image_owner_exposed"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/jecoute/resources",
 *             "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')",
 *         }
 *     },
 *     itemOperations={}
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\ResourceRepository")
 * @ORM\Table(
 *     name="jecoute_resource",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"uuid"})
 *     }
 * )
 */
class Resource implements ExposedImageOwnerInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use ImageTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Groups({"jecoute_resources_read"})
     */
    private ?string $label;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Url
     *
     * @Groups({"jecoute_resources_read"})
     */
    private ?string $url;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     mimeTypes={"image/jpeg", "image/png"},
     *     maxSize="5M",
     * )
     */
    protected $image;

    /**
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition
     */
    private int $position;

    public function __construct(UuidInterface $uuid = null, string $label = null, string $url = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->label = $label;
        $this->url = $url;
    }

    public function __toString()
    {
        return $this->label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
