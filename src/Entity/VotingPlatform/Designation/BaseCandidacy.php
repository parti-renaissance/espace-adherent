<?php

namespace App\Entity\VotingPlatform\Designation;

use App\Entity\ImageTrait;
use App\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class BaseCandidacy implements CandidacyInterface
{
    use TimestampableEntity;
    use ImageTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $uuid;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $gender;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max=500)
     */
    private $biography;

    /**
     * @var UploadedFile|null
     *
     * @Assert\Image(
     *     maxSize="5M",
     *     mimeTypes={"image/jpeg", "image/png"}
     * )
     */
    protected $image;

    private $removeImage = false;

    public function __construct(string $gender = null, UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->gender = $gender;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getCivility(): string
    {
        return $this->isMale() ? 'M.' : 'Mme.';
    }

    public function isMale(): bool
    {
        return Genders::MALE === $this->gender;
    }

    public function isFemale(): bool
    {
        return Genders::FEMALE === $this->gender;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): void
    {
        $this->biography = $biography;
    }

    public function getImagePath(): string
    {
        return $this->getImageName() ?
            sprintf('images/candidacies/profile/%s', $this->getImageName())
            : ''
        ;
    }

    public function isRemoveImage(): bool
    {
        return $this->removeImage;
    }

    public function setRemoveImage(bool $value): void
    {
        $this->removeImage = $value;
    }
}
