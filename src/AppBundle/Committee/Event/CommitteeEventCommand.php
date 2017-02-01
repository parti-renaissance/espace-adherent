<?php

namespace AppBundle\Committee\Event;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CommitteeEventCommand
{
    private $uuid;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=100)
     */
    private $name;

    /**
     * @Assert\NotBlank
     * @Assert\Choice(
     *   callback="AppBundle\Committee\Event\EventCategories::all",
     *   strict=true,
     *   message="committee.event.invalid_category"
     * )
     */
    private $category;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=10)
     */
    private $description;

    /**
     * @Assert\Regex("/^\d+$/", message="committee.event.invalid_capacity")
     */
    private $capacity;

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
    private $committee;
    private $committeeEvent;

    public function __construct(
        Adherent $author,
        Committee $committee,
        UuidInterface $uuid = null,
        Address $address = null,
        \DateTimeInterface $beginAt = null,
        \DateTimeInterface $finishAt = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->committee = $committee;
        $this->author = $author;
        $this->address = $address ?: new Address();
        $this->beginAt = $beginAt ?: new \DateTime(date('Y-m-d 00:00:00'));
        $this->finishAt = $finishAt ?: new \DateTime(date('Y-m-d 23:59:59'));
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category)
    {
        $this->category = $category;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getCapacity(): ?int
    {
        return null !== $this->capacity ? (int) $this->capacity : null;
    }

    public function setCapacity($capacity)
    {
        if (null !== $capacity) {
            $capacity = (string) $capacity;
        }

        $this->capacity = $capacity;
    }

    public function getBeginAt(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTime $beginAt)
    {
        $this->beginAt = $beginAt;
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(\DateTime $finishAt)
    {
        $this->finishAt = $finishAt;
    }

    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    /**
     * @Assert\Callback
     */
    public static function validateDateRange(self $command, ExecutionContextInterface $context)
    {
        $beginAt = $command->getBeginAt();
        $finishAt = $command->getFinishAt();

        if (!$beginAt instanceof \DateTimeInterface || !$finishAt instanceof \DateTimeInterface) {
            return;
        }

        if ($finishAt <= $beginAt) {
            $context
                ->buildViolation('committee.event.invalid_date_range')
                ->atPath('finishAt')
                ->addViolation();
        }
    }

    public function setCommitteeEvent(CommitteeEvent $event)
    {
        $this->committeeEvent = $event;
    }

    public function getCommitteeEvent(): ?CommitteeEvent
    {
        return $this->committeeEvent;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
