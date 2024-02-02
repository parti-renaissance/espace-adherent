<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommitmentRepository")
 */
class Commitment implements ImageOwnerInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use ImageTrait;
    use Sortable;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    public ?string $title = null;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    public ?string $shortDescription = null;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    public ?string $description = null;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize="5M",
     *     mimeTypes={"image/jpeg", "image/png"}
     * )
     */
    protected $image;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getImagePath(): string
    {
        return $this->imageName ? sprintf('images/commitments/%s', $this->getImageName()) : '';
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
