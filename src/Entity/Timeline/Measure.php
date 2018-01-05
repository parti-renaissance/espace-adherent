<?php

namespace AppBundle\Entity\Timeline;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="timeline_measures")
 * @ORM\Entity
 */
class Measure
{
    public const TITLE_MAX_LENGTH = 100;

    public const STATUS_UPCOMING = 'UPCOMING';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_DONE = 'DONE';
    public const STATUS_DEFERRED = 'DEFERRED';

    public const STATUSES = [
        'À venir' => self::STATUS_UPCOMING,
        'En cours' => self::STATUS_IN_PROGRESS,
        'Fait' => self::STATUS_DONE,
        'Reporté' => self::STATUS_DEFERRED,
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Algolia\Attribute
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=Measure::TITLE_MAX_LENGTH)
     *
     * @Algolia\Attribute
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     *
     * @Algolia\Attribute
     */
    private $link;

    /**
     * @var string|null
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank
     * @Assert\Choice(
     *      choices=Measure::STATUSES,
     *      strict=true
     * )
     *
     * @Algolia\Attribute
     */
    private $status;

    /**
     * @var \DateTime|null
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Algolia\Attribute
     */
    private $global = false;

    /**
     * @var Profile[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Timeline\Profile")
     * @ORM\JoinTable(
     *     name="timeline_measures_profiles",
     *     joinColumns={
     *         @ORM\JoinColumn(name="measure_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     *     }
     * )
     *
     * @Algolia\Attribute
     */
    private $profiles;

    /**
     * @param string      $title
     * @param string      $status
     * @param Profile[]   $profiles
     * @param string|null $link
     * @param bool|null   $isGlobal
     */
    public function __construct(
        string $title,
        string $status,
        array $profiles = [],
        ?string $link = null,
        ?bool $isGlobal = false
    ) {
        $this->title = $title;
        $this->status = $status;
        $this->link = $link;
        $this->global = $isGlobal;
        $this->profiles = new ArrayCollection($profiles);
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @Algolia\Attribute
     */
    public function getFormattedUpdatedAt(): ?string
    {
        if (!$this->updatedAt) {
            return null;
        }

        return $this->updatedAt->format('Y-m-d H:i:s');
    }

    public function isGlobal(): bool
    {
        return $this->global;
    }

    public function setGlobal(bool $global): void
    {
        $this->global = $global;
    }

    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    public function addProfile(Profile $profile): void
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles->add($profile);
        }
    }

    public function removeProfile(Profile $profile): void
    {
        $this->profiles->removeElement($profile);
    }

    public function isUpcoming(): bool
    {
        return self::STATUS_UPCOMING === $this->status;
    }

    public function isInProgress(): bool
    {
        return self::STATUS_IN_PROGRESS === $this->status;
    }

    public function isDone(): bool
    {
        return self::STATUS_DONE === $this->status;
    }

    public function isDeferred(): bool
    {
        return self::STATUS_DEFERRED === $this->status;
    }

    public function equals(self $measure): bool
    {
        return $measure->title === $this->title;
    }
}
