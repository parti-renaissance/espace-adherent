<?php

namespace App\Entity\Coalition;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\Event\CauseEvent;
use App\Entity\ImageOwnerInterface;
use App\Entity\ImageTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "pagination_client_items_per_page": true,
 *         "order": {"name": "ASC"}
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/causes",
 *             "normalization_context": {
 *                 "groups": {"cause_read"}
 *             },
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/causes/{id}",
 *             "normalization_context": {"groups": {"cause_read"}},
 *             "requirements": {"id": "%pattern_uuid%"}
 *         }
 *     },
 * )
 *
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="cause_uuid_unique", columns="uuid"),
 *         @ORM\UniqueConstraint(name="cause_name_unique", columns="name")
 *     }
 * )
 * @ORM\Entity
 */
class Cause implements ImageOwnerInterface
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
     * @var string|null
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"cause_read"})
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @SymfonySerializer\Groups({"cause_read"})
     */
    private $description;

    /**
     * @var Coalition|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Coalition", inversedBy="causes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $coalition;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @var CauseEvent[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Event\CauseEvent",
     *     mappedBy="cause",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $events;

    public function __construct(
        UuidInterface $uuid = null,
        string $name = null,
        string $description = null,
        Coalition $coalition = null,
        Adherent $author = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;
        $this->description = $description;
        $this->coalition = $coalition;
        $this->author = $author;

        $this->events = new ArrayCollection();
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

    public function setDescription(string $description): void
    {
        $this->description = $description;
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
        return $this->imageName ? \sprintf('images/causes/%s', $this->getImageName()) : '';
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(CauseEvent $event): void
    {
        if (!$this->events->contains($event)) {
            $event->setCause($this);
            $this->events->add($event);
        }
    }

    public function removeEvent(CauseEvent $event): void
    {
        $this->events->removeElement($event);
    }

    public function getCoalition(): ?Coalition
    {
        return $this->coalition;
    }

    public function setCoalition(Coalition $coalition): void
    {
        $this->coalition = $coalition;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }
}
