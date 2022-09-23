<?php

namespace App\Entity\Renaissance;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Renaissance\Newsletter\SubscriptionRequest;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Renaissance\NewsletterSubscriptionRepository")
 * @ORM\Table(name="renaissance_newsletter_subscription")
 */
class NewsletterSubscription
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column
     */
    public string $firstName;

    /**
     * @ORM\Column
     */
    public string $zipCode;

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

        $object->firstName = $request->firstName;
        $object->zipCode = $request->zipCode;
        $object->email = $request->email;

        return $object;
    }
}
