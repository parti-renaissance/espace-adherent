<?php

namespace App\Entity\Renaissance;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\NewsletterSubscriptionInterface;
use App\Renaissance\Newsletter\SubscriptionRequest;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Renaissance\NewsletterSubscriptionRepository")
 * @ORM\Table(name="renaissance_newsletter_subscription")
 */
class NewsletterSubscription implements NewsletterSubscriptionInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $firstName = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $zipCode = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $source = null;

    /**
     * @ORM\Column(unique=true)
     */
    public string $email;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public ?\DateTime $confirmedAt = null;

    /**
     * @ORM\Column(type="uuid")
     */
    public UuidInterface $token;

    private function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->token = Uuid::uuid4();
    }

    public static function create(SubscriptionRequest $request): self
    {
        $object = new self();

        $object->zipCode = $request->postalCode;
        $object->email = $request->email;
        $object->source = $request->source;

        return $object;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getToken(): ?UuidInterface
    {
        return $this->token;
    }

    public function isConfirmed(): bool
    {
        return null !== $this->confirmedAt;
    }
}
