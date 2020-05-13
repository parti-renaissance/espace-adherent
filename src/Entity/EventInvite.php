<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Event\EventInvitation;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="events_invitations")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class EventInvite
{
    use EntityIdentityTrait;
    use EntityPersonNameTrait;

    /**
     * @var BaseEvent|null
     *
     * @ORM\ManyToOne(targetEntity="BaseEvent")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    private $event;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $email = '';

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $message = '';

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $guests = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(BaseEvent $event)
    {
        $this->uuid = Uuid::uuid4();
        $this->event = $event;
        $this->createdAt = new \DateTime();
    }

    public function __toString()
    {
        return sprintf('Invitation à l\'évenement %s de %s', $this->event, $this->getFullName());
    }

    public static function create(BaseEvent $event, EventInvitation $invitation): self
    {
        $invite = new static($event);
        $invite->firstName = $invitation->firstName;
        $invite->lastName = $invitation->lastName;
        $invite->email = $invitation->email;
        $invite->message = $invitation->message ?: '';
        $invite->guests = $invitation->guests;

        return $invite;
    }

    public function getEvent(): ?BaseEvent
    {
        return $this->event;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getGuests(): array
    {
        return $this->guests;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
