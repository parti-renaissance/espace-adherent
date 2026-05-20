<?php

declare(strict_types=1);

namespace App\Entity\Renaissance;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\NewsletterSubscriptionInterface;
use App\Renaissance\Newsletter\SubscriptionRequest;
use App\Repository\Renaissance\NewsletterSubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: NewsletterSubscriptionRepository::class)]
#[ORM\Table(name: 'renaissance_newsletter_subscription')]
class NewsletterSubscription implements NewsletterSubscriptionInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column(nullable: true)]
    public ?string $firstName = null;

    #[ORM\Column(nullable: true)]
    public ?string $lastName = null;

    #[ORM\Column(nullable: true)]
    public ?string $zipCode = null;

    #[ORM\Column(nullable: true)]
    public ?string $source = null;

    #[ORM\Column(unique: true)]
    public string $email;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $confirmedAt = null;

    #[ORM\Column(type: 'uuid')]
    public Uuid $token;

    private function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->token = Uuid::v4();
    }

    public static function create(SubscriptionRequest $request): self
    {
        $object = new self();
        $object->email = $request->email;

        $object->updateFromRequest($request);

        return $object;
    }

    public function updateFromRequest(SubscriptionRequest $request): void
    {
        $this->firstName = $request->firstName ?: $this->firstName;
        $this->lastName = $request->lastName ?: $this->lastName;
        $this->zipCode = $request->postalCode ?: $this->zipCode;
        $this->source = $request->source ?: $this->source;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getToken(): ?Uuid
    {
        return $this->token;
    }

    public function isConfirmed(): bool
    {
        return null !== $this->confirmedAt;
    }
}
