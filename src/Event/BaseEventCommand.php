<?php

namespace AppBundle\Event;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\BaseEventCategory;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BaseEventCommand
{
    /**
     * @var BaseEvent|null
     */
    protected $event;

    /**
     * @Assert\NotNull
     */
    protected $category;

    private $uuid;
    private $author;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=100)
     */
    private $name;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=10)
     */
    private $description;

    /**
     * @Assert\NotBlank
     */
    private $beginAt;

    /**
     * @Assert\NotBlank
     * @Assert\Expression("value > this.getBeginAt()", message="committee.event.invalid_date_range")
     */
    private $finishAt;

    /**
     * @Assert\NotBlank
     * @Assert\Valid
     */
    private $address;

    protected function __construct(
        ?Adherent $author, // Author may be null if unregistered when editing an event
        UuidInterface $uuid = null,
        Address $address = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null,
        BaseEvent $event = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->author = $author;
        $this->address = $address ?: new Address();
        $this->beginAt = $beginAt ?: new \DateTimeImmutable(date('Y-m-d 00:00:00'));
        $this->finishAt = $finishAt ?: new \DateTimeImmutable(date('Y-m-d 23:59:59'));

        if ($event) {
            $this->name = $event->getName();
            $this->description = $event->getDescription();
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCategory(): ?BaseEventCategory
    {
        return $this->category;
    }

    public function setCategory(?BaseEventCategory $category): void
    {
        $categoryClass = $this->getCategoryClass();

        if (!$category instanceof $categoryClass) {
            throw new \InvalidArgumentException('Expected an instance of "%s" but got "%s".', $categoryClass, get_class($category));
        }

        $this->category = $category;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getBeginAt(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(?\DateTimeInterface $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeInterface $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    protected function getCategoryClass(): string
    {
        throw new \LogicException(sprintf('The method "%s" must be overridden in "%s".', __METHOD__, static::class));
    }

    final protected static function getAddressModelFromEvent(BaseEvent $event): Address
    {
        return Address::createFromAddress($event->getPostAddressModel());
    }
}
