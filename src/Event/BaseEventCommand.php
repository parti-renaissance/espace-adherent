<?php

namespace App\Event;

use App\Address\Address;
use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\Event;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class BaseEventCommand
{
    /**
     * @var Event|null
     */
    protected $event;

    #[Assert\NotNull(groups: ['with_category'])]
    protected $category;

    private $uuid;
    private $author;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 5, max: 100),
    ])]
    private $name;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 10),
    ])]
    private $description;

    #[Assert\NotBlank]
    #[Assert\Timezone]
    private $timeZone;

    #[Assert\NotBlank]
    private $beginAt;

    #[Assert\Expression('value > this.getBeginAt()', message: 'committee.event.invalid_date_range')]
    #[Assert\NotBlank]
    private $finishAt;

    #[Assert\NotBlank]
    #[Assert\Valid]
    private $address;

    #[Assert\Url]
    private $visioUrl;

    private $image;

    private $removeImage = false;

    /**
     * @var string|null
     */
    #[Assert\Choice(choices: Event::MODES)]
    private $mode;

    /**
     * @param ?Adherent $author Author may be null if unregistered when editing an event
     */
    protected function __construct(
        ?Adherent $author,
        ?UuidInterface $uuid = null,
        ?Address $address = null,
        ?\DateTimeInterface $beginAt = null,
        ?\DateTimeInterface $finishAt = null,
        ?Event $event = null,
        string $timeZone = GeoCoder::DEFAULT_TIME_ZONE,
        ?string $visioUrl = null,
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->author = $author;
        $this->address = $address ?: new Address();
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->timeZone = $timeZone;
        $this->visioUrl = $visioUrl;

        if ($event) {
            $this->name = $event->getName();
            $this->description = $event->getDescription();
            $this->timeZone = $event->getTimeZone();
            $this->visioUrl = $event->getVisioUrl();
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
            throw new \InvalidArgumentException('Expected an instance of "%s" but got "%s".', $categoryClass, $category::class);
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

    public function getTimeZone(): ?string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): void
    {
        $this->timeZone = $timeZone;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function getVisioUrl(): ?string
    {
        return $this->visioUrl;
    }

    public function setVisioUrl(?string $visioUrl): void
    {
        $this->visioUrl = $visioUrl;
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    public function setImage(?UploadedFile $image): void
    {
        $this->image = $image;
    }

    public function isRemoveImage(): bool
    {
        return $this->removeImage;
    }

    public function setRemoveImage(bool $value): void
    {
        $this->removeImage = $value;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): void
    {
        $this->mode = $mode;
    }

    protected function getCategoryClass(): string
    {
        throw new \LogicException(\sprintf('The method "%s" must be overridden in "%s".', __METHOD__, static::class));
    }

    final protected static function getAddressModelFromEvent(Event $event): Address
    {
        return Address::createFromAddress($event->getPostAddress());
    }
}
