<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\EntityNameSlugTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @ORM\Table(
 *     name="note_note",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="note_name_unique", columns="name"),
 *         @ORM\UniqueConstraint(name="note_slug_unique", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("name")
 * @UniqueEntity("slug")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Note
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    private const PUBLISHED_INTERVAL = 'P3W';

    /**
     * @ORM\ManyToOne(targetEntity="Theme")
     */
    private $theme;

    /**
     * @ORM\ManyToOne(targetEntity="Scale")
     */
    private $scale;

    /**
     * @ORM\ManyToMany(targetEntity="Need")
     * @ORM\JoinTable(name="note_notes_needs")
     */
    private $needs;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     */
    private $adherent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishedAt;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     */
    private $committee;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\IdeasWorkshop\Guideline")
     * @ORM\JoinTable(name="note_notes_guidelines")
     */
    private $guidelines;

    /**
     * @Assert\Choice(
     *     callback={"AppBundle\Entity\IdeasWorkshop\NoteStatusEnum", "toArray"},
     *     strict=true,
     * )
     *
     * @ORM\Column(length=11, options={"default": NoteStatusEnum::IN_PROGRESS})
     */
    private $status;

    public function __construct()
    {
        $this->needs = new ArrayCollection();
        $this->guidelines = new ArrayCollection();
    }

    public static function create(
        UuidInterface $uuid,
        string $name,
        Adherent $adherent,
        string $status = NoteStatusEnum::IN_PROGRESS
    ): Note {
        $note = new self();

        $note->uuid = $uuid;
        $note->setName($name);
        $note->status = $status;
        $note->setAdherent($adherent);

        return $note;
    }

    public static function createUuid(string $name): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $name);
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme): void
    {
        $this->theme = $theme;
    }

    public function getScale(): ?Scale
    {
        return $this->scale;
    }

    public function setScale(Scale $scale): void
    {
        $this->scale = $scale;
    }

    public function getNeeds(): ArrayCollection
    {
        return $this->needs;
    }

    public function addNeed(Need $need): void
    {
        if (!$this->needs->contains($need)) {
            $this->needs->add($need);
        }
    }

    public function removeNeed(Need $need): void
    {
        $this->needs->removeElement($need);
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function addGuideline(Guideline $guideline): void
    {
        if (!$this->guidelines->contains($guideline)) {
            $this->guidelines->add($guideline);
        }
    }

    public function removeGuideline(Guideline $guideline): void
    {
        $this->guidelines->removeElement($guideline);
    }

    public function getGuidelines(): ArrayCollection
    {
        return $this->guidelines;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getDaysBeforeDeadline(): int
    {
        $deadline = $this->createdAt->add(new \DateInterval(self::PUBLISHED_INTERVAL));
        $now = new \DateTime();

        return $deadline > $now ? 0 : $deadline->diff($now)->d;
    }

    public function isInProgress(): bool
    {
        return NoteStatusEnum::IN_PROGRESS === $this->status;
    }

    public function isPublished(): bool
    {
        return NoteStatusEnum::PUBLISHED === $this->status;
    }

    public function isRefused(): bool
    {
        return NoteStatusEnum::REFUSED === $this->status;
    }
}
