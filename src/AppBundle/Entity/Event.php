<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Event\EventCategories;
use AppBundle\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 * @ORM\Table(
 *   name="events",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="event_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="event_slug_unique", columns="slug")
 *   }
 * )
 *
 * @Algolia\Index()
 */
class Event implements GeoPointInterface
{
    const STATUS_SCHEDULED = 'SCHEDULED';
    const STATUS_CANCELLED = 'CANCELLED';

    const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_CANCELLED,
    ];

    const ACTIVE_STATUSES = [
        self::STATUS_SCHEDULED,
    ];

    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(length=100)
     *
     * @Algolia\Attribute
     */
    private $name;

    /**
     * The event canonical name.
     *
     * @ORM\Column(length=100)
     *
     * @Algolia\Attribute
     */
    private $canonicalName;

    /**
     * @ORM\Column(length=130)
     * @Gedmo\Slug(fields={"beginAt", "canonicalName"}, dateFormat="Y-m-d")
     *
     * @Algolia\Attribute
     */
    private $slug;

    /**
     * @ORM\Column(length=5)
     *
     * @Algolia\Attribute
     */
    private $category;

    /**
     * @ORM\Column(type="text")
     *
     * @Algolia\Attribute
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $capacity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $beginAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $finishAt;

    /**
     * The adherent UUID who created this committee event.
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $organizer;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $participantsCount;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     *
     * @Algolia\Attribute
     */
    private $committee;

    /**
     * @ORM\Column(length=20)
     */
    private $status;

    public function __construct(
        UuidInterface $uuid,
        Adherent $organizer,
        ?Committee $committee,
        string $name,
        string $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt,
        int $capacity = null,
        string $slug = null,
        string $createdAt = null,
        int $participantsCount = 0
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->committee = $committee;
        $this->setName($name);
        $this->slug = $slug;
        $this->category = $category;
        $this->description = $description;
        $this->postAddress = $address;
        $this->capacity = $capacity;
        $this->participantsCount = $participantsCount;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->createdAt = new \DateTime($createdAt ?: 'now');
        $this->updatedAt = new \DateTime($createdAt ?: 'now');
        $this->status = self::STATUS_SCHEDULED;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function update(
        string $name,
        string $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt,
        int $capacity = null
    ) {
        $this->setName($name);
        $this->category = $category;
        $this->capacity = $capacity;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->description = $description;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    private static function canonicalize(string $name)
    {
        return mb_strtolower($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getCategoryName(): string
    {
        return EventCategories::getCategoryName($this->category);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function getBeginAt(): \DateTimeInterface
    {
        return $this->beginAt;
    }

    public function getFinishAt(): \DateTimeInterface
    {
        return $this->finishAt;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getOrganizer(): ?Adherent
    {
        return $this->organizer;
    }

    public function getOrganizerName(): ?string
    {
        return $this->organizer ? $this->organizer->getFirstName() : '';
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getParticipantsCount(): int
    {
        return $this->participantsCount;
    }

    public function incrementParticipantsCount(int $increment = 1)
    {
        $this->participantsCount += $increment;
    }

    public function decrementParticipantsCount(int $increment = 1)
    {
        $this->participantsCount -= $increment;
    }

    public function updatePostAddress(PostAddress $postAddress)
    {
        if (!$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
    }

    private function setName(string $name)
    {
        $this->name = $name;
        $this->canonicalName = static::canonicalize($name);
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    public function isFinished(): bool
    {
        // The production web server is configured with Europe/Paris timezone.
        // So if the event happens in France, then we can compare its ending
        // date and time with the current time.
        if ('FR' === $country = $this->getCountry()) {
            return $this->finishAt < new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        }

        // However, for an event taking place in another country in the world,
        // we need to know the timezone of this country. Some large countries
        // like the United States, Canada, Russia or Australia have multiple
        // timezones. Since we cannot accurately know the timezone of the event
        // taking place in a foreign country, the algorithm below will make the
        // following simple assumption.
        //
        // If there is at least one timezone for which the event is considered
        // not finished, then the method will return false. However, if the
        // event is finished in all timezones of this country, then the method
        // can return true.
        foreach (\DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country) as $timezone) {
            $finishAt = new \DateTime($this->finishAt->format('Y-m-d H:i'), $timezone = new \DateTimeZone($timezone));
            if (false === $finishAt < new \DateTime('now', $timezone)) {
                return false;
            }
        }

        return true;
    }

    public function isFull(): bool
    {
        if (!$this->capacity) {
            return false;
        }

        return $this->participantsCount >= $this->capacity;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid status "%" given.', $status);
        }

        $this->status = $status;
    }

    public function cancel()
    {
        $this->status = self::STATUS_CANCELLED;
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isCancelled(): bool
    {
        return self::STATUS_CANCELLED === $this->status;
    }

    /**
     * @Algolia\Attribute(algoliaName="begin_at")
     */
    public function getReadableCreatedAt(): string
    {
        return $this->beginAt->format('d/m/Y H:i');
    }

    /**
     * @Algolia\Attribute(algoliaName="finish_at")
     */
    public function getReadableUpdatedAt(): string
    {
        return $this->finishAt->format('d/m/Y H:i');
    }
}
