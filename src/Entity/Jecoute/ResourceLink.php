<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\ExposedImageOwnerInterface;
use App\Entity\ImageTrait;
use App\Repository\Jecoute\ResourceLinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order": {"position": "ASC"},
 *         "normalization_context": {"groups": {"jecoute_resource_links_read", "image_owner_exposed"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/jecoute/resource-links",
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')",
 *         }
 *     },
 *     itemOperations={}
 * )
 */
#[ORM\Table(name: 'jecoute_resource_link')]
#[ORM\Entity(repositoryClass: ResourceLinkRepository::class)]
class ResourceLink implements ExposedImageOwnerInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use ImageTrait;
    use Sortable;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[Groups(['jecoute_resource_links_read'])]
    #[ORM\Column]
    private ?string $label;

    /**
     * @Assert\NotBlank
     * @Assert\Url
     */
    #[Groups(['jecoute_resource_links_read'])]
    #[ORM\Column(type: 'text')]
    private ?string $url;

    /**
     * @Assert\Image(
     *     mimeTypes={"image/jpeg", "image/png"},
     *     maxSize="5M",
     * )
     */
    protected $image;

    public function __construct(?UuidInterface $uuid = null, ?string $label = null, ?string $url = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->label = $label;
        $this->url = $url;
    }

    public function __toString(): string
    {
        return (string) $this->label;
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

    public function getImagePath(): string
    {
        return $this->imageName ? sprintf('images/jecoute/resources/%s', $this->getImageName()) : '';
    }
}
