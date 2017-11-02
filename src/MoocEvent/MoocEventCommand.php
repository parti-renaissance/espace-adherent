<?php

namespace AppBundle\MoocEvent;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Group;
use AppBundle\Entity\MoocEvent;
use AppBundle\Entity\MoocEventCategory;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MoocEventCommand
{
    private $uuid;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=100)
     */
    private $name;

    /**
     * @Assert\NotNull
     */
    private $category;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=10)
     */
    private $description;

    /**
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $beginAt;

    /**
     * @Assert\NotBlank
     * @Assert\DateTime
     */
    private $finishAt;

    /**
     * @var Address
     *
     * @Assert\NotBlank
     * @Assert\Valid
     */
    private $address;

    private $author;
    private $group;
    private $moocEvent;

    /**
     * @Assert\Regex("/^\d+$/", message="mooc_event.invalid_capacity")
     */
    private $capacity;

    public function __construct(
        Adherent $author = null,
        Group $group = null,
        UuidInterface $uuid = null,
        Address $address = null,
        int $capacity = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->author = $author;
        $this->group = $group;
        $this->address = $address ?: new Address();
        $this->capacity = $capacity;
        $this->beginAt = $beginAt ?: new \DateTime(date('Y-m-d 00:00:00'));
        $this->finishAt = $finishAt ?: new \DateTime(date('Y-m-d 23:59:59'));
    }

    public static function createFromMoocEvent(MoocEvent $moocEvent): self
    {
        $command = new self(
            $moocEvent->getOrganizer(),
            $moocEvent->getUuid(),
            Address::createFromAddress($moocEvent->getPostAddressModel()),
            $moocEvent->getCapacity(),
            $moocEvent->getBeginAt(),
            $moocEvent->getFinishAt()
        );

        $command->name = $moocEvent->getName();
        $command->category = $moocEvent->getCategory();
        $command->description = $moocEvent->getDescription();

        return $command;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCategory(): ?MoocEventCategory
    {
        return $this->category;
    }

    public function setCategory(?MoocEventCategory $category = null): void
    {
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

    public function setBeginAt(\DateTime $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(\DateTime $finishAt): void
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

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * @Assert\Callback
     */
    public static function validateDateRange(self $command, ExecutionContextInterface $context): void
    {
        $beginAt = $command->getBeginAt();
        $finishAt = $command->getFinishAt();

        if (!$beginAt instanceof \DateTimeInterface || !$finishAt instanceof \DateTimeInterface) {
            return;
        }

        if ($finishAt <= $beginAt) {
            $context
                ->buildViolation('mooc_event.invalid_date_range')
                ->atPath('finishAt')
                ->addViolation();
        }
    }

    public function setMoocEvent(MoocEvent $moocEvent): void
    {
        $this->moocEvent = $moocEvent;
    }

    public function getMoocEvent(): ?MoocEvent
    {
        return $this->moocEvent;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getCapacity(): ?int
    {
        return null !== $this->capacity ? (int) $this->capacity : null;
    }

    public function setCapacity($capacity): void
    {
        if (null !== $capacity) {
            $capacity = (string) $capacity;
        }

        $this->capacity = $capacity;
    }
}
