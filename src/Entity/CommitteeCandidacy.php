<?php

namespace AppBundle\Entity;

use AppBundle\ValueObject\Genders;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeCandidacyRepository")
 */
class CommitteeCandidacy implements ImageOwnerInterface
{
    use TimestampableEntity;
    use ImageTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @Assert\Length(max="2000")
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

    /**
     * @var CommitteeElection
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CommitteeElection")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $committeeElection;

    private $removeImage = false;

    public function __construct(CommitteeElection $election, string $gender = null)
    {
        $this->committeeElection = $election;
        $this->gender = $gender;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommitteeElection(): CommitteeElection
    {
        return $this->committeeElection;
    }

    public function setCommitteeElection(CommitteeElection $committeeElection): void
    {
        $this->committeeElection = $committeeElection;
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
        return sprintf('images/candidacies/profile/%s', $this->getImageName());
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
