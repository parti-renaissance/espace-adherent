<?php

declare(strict_types=1);

namespace App\Entity\Jecoute;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\ImageExposeInterface;
use App\Entity\ImageManageableInterface;
use App\Entity\ImageTrait;
use App\Normalizer\ImageExposeNormalizer;
use App\Repository\Jecoute\ResourceLinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/v3/jecoute/resource-links',
            security: "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')"
        ),
    ],
    normalizationContext: ['groups' => ['jecoute_resource_links_read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
    order: ['position' => 'ASC']
)]
#[ORM\Entity(repositoryClass: ResourceLinkRepository::class)]
#[ORM\Table(name: 'jecoute_resource_link')]
class ResourceLink implements \Stringable, ImageManageableInterface, ImageExposeInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use ImageTrait;
    use Sortable;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['jecoute_resource_links_read'])]
    #[ORM\Column]
    private ?string $label;

    #[Assert\NotBlank]
    #[Assert\Url]
    #[Groups(['jecoute_resource_links_read'])]
    #[ORM\Column(type: 'text')]
    private ?string $url;

    #[Assert\Image(mimeTypes: ['image/jpeg', 'image/png'], maxSize: '5M')]
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
        return $this->imageName ? \sprintf('images/jecoute/resources/%s', $this->getImageName()) : '';
    }
}
