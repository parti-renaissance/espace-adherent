<?php

declare(strict_types=1);

namespace App\Entity\Event;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPersonNameTrait;
use App\Event\EventInvitation;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'events_invitations')]
class EventInvite implements \Stringable
{
    use EntityIdentityTrait;
    use EntityPersonNameTrait;

    /**
     * @var Event|null
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Event::class)]
    private $event;

    /**
     * @var string
     */
    #[ORM\Column]
    private $email = '';

    /**
     * @var string
     */
    #[ORM\Column(type: 'text')]
    private $message = '';

    /**
     * @var array
     */
    #[ORM\Column(type: 'simple_array')]
    private $guests = [];

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    public function __construct(Event $event)
    {
        $this->uuid = Uuid::uuid4();
        $this->event = $event;
        $this->createdAt = new \DateTime();
    }

    public function __toString()
    {
        return \sprintf('Invitation à l\'évenement %s de %s', $this->event, $this->getFullName());
    }

    public static function create(Event $event, EventInvitation $invitation): self
    {
        $invite = new self($event);
        $invite->firstName = $invitation->firstName;
        $invite->lastName = $invitation->lastName;
        $invite->email = $invitation->email;
        $invite->message = $invitation->message ?: '';
        $invite->guests = $invitation->guests;

        return $invite;
    }

    public function getEvent(): ?Event
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
