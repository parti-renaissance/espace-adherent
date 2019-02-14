<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\Entity\EnabledInterface;
use AppBundle\Entity\ImageTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"theme_read"}
 *         },
 *         "order": {"position": "ASC"},
 *         "pagination_enabled": false,
 *     },
 *     collectionOperations={"get": {"path": "/ideas-workshop/themes"}},
 * )
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="ideas_workshop_theme",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="theme_name_unique", columns="name")
 *     }
 * )
 *
 * @UniqueEntity("name")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Theme implements EnabledInterface
{
    use ImageTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     *
     * @SymfonySerializer\Groups({"theme_read", "idea_read", "idea_list_read"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"theme_read", "idea_list_read", "idea_read"})
     */
    protected $name;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize="10M",
     *     binaryFormat=false,
     *     mimeTypes={"image/png", "image/svg+xml"}
     * )
     */
    protected $image;

    /**
     * @ORM\Column
     */
    protected $imageName;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Assert\GreaterThanOrEqual(1)
     *
     * @Gedmo\SortablePosition
     */
    private $position;

    public function __construct(string $name = '', string $imageName = null, bool $enabled = false, int $position = 1)
    {
        $this->name = $name;
        $this->imageName = $imageName;
        $this->enabled = $enabled;
        $this->position = $position;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getImagePath(): string
    {
        return sprintf('images/ideas_workshop/themes/%s', $this->getImageName());
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }
}
