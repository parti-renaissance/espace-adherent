<?php

namespace App\Entity\Coalition;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\Event\CoalitionEvent;
use App\Entity\ImageOwnerInterface;
use App\Entity\ImageTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="coalition_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="coalition_name_unique", columns="name")
 *     }
 * )
 * @ORM\Entity
 */
class Coalition implements ImageOwnerInterface
{
    use EntityIdentityTrait;
    use TimestampableEntity;
    use ImageTrait;

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
     * @var string
     *
     * @ORM\Column
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $enabled = true;

    /**
     * @var CoalitionEvent[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Event\CoalitionEvent",
     *     mappedBy="coalition",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $events;

    /**
     * @var Collection|Adherent[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Adherent")
     * @ORM\JoinTable(
     *     name="coalition_follower",
     *     joinColumns={
     *         @ORM\JoinColumn(name="coalition_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="adherent_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    protected $followers;

    public function __construct(
        UuidInterface $uuid = null,
        string $name = null,
        string $description = null,
        bool $enabled = true
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;
        $this->description = $description;
        $this->enabled = $enabled;

        $this->events = new ArrayCollection();
        $this->followers = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setImageName(?UploadedFile $image): void
    {
        $this->imageName = null === $image ? null :
            sprintf('%s.%s',
                $this->uuid,
                $image->getClientOriginalExtension()
            )
        ;
    }

    public function getImagePath(): string
    {
        return sprintf('images/coalitions/%s', $this->getImageName());
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(CoalitionEvent $event): void
    {
        if (!$this->events->contains($event)) {
            $event->setCoalition($this);
            $this->events->add($event);
        }
    }

    public function removeEvent(CoalitionEvent $event): void
    {
        $this->events->removeElement($event);
    }

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(Adherent $follower): void
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
        }
    }

    public function removeFollower(Adherent $follower): void
    {
        $this->followers->remove($follower);
    }
}
