<?php

namespace App\Entity;

use App\Repository\NewsletterInviteRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: NewsletterInviteRepository::class)]
#[ORM\Table(name: 'newsletter_invitations')]
class NewsletterInvite
{
    use EntityIdentityTrait;

    /**
     * @var string
     */
    #[ORM\Column(length: 50)]
    private $firstName = '';

    /**
     * @var string
     */
    #[ORM\Column(length: 50)]
    private $lastName = '';

    /**
     * @var string
     */
    #[ORM\Column]
    private $email = '';

    /**
     * @var string|null
     */
    #[ORM\Column(length: 50, nullable: true)]
    private $clientIp;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->createdAt = new \DateTime();
    }

    public function __toString()
    {
        return 'Invitation à la newsletter de '.$this->getSenderFullName().' à '.$this->email;
    }

    public static function create(string $firstName, string $lastName, string $email, string $clientIp): self
    {
        $invite = new self();

        $invite->firstName = $firstName;
        $invite->lastName = $lastName;
        $invite->email = $email;
        $invite->clientIp = $clientIp;

        return $invite;
    }

    public function getSenderFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt ? \DateTimeImmutable::createFromMutable($this->createdAt) : null;
    }
}
