<?php

namespace App\Entity;

use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as AssertUniqueEntity;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueEntity(fields={"email"}, message="newsletter.already_registered")
 *
 * @ORM\Table(name="newsletter_subscriptions")
 * @ORM\Entity(repositoryClass="App\Repository\NewsletterSubscriptionRepository")
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @AssertRecaptcha(groups={"Subscription"})
 */
class NewsletterSubscription implements NewsletterSubscriptionInterface, EntitySoftDeletedInterface, RecaptchaChallengeInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use RecaptchaChallengeTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, unique=true)
     *
     * @Assert\NotBlank(message="newsletter.email.not_blank")
     * @Assert\Email(message="newsletter.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=11, nullable=true)
     *
     * @Assert\Length(
     *     min=2,
     *     max=11,
     *     minMessage="newsletter.postalCode.invalid",
     *     maxMessage="newsletter.postalCode.invalid"
     * )
     */
    private $postalCode;

    /**
     * The address country code (ISO2).
     *
     * @var string
     *
     * @ORM\Column(length=2, nullable=true)
     */
    private $country;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $fromEvent;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $confirmedAt;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid", unique=true, nullable=true)
     */
    private $token;

    /**
     * @var bool
     */
    private $personalDataCollection = false;

    public function __construct(
        ?string $email = null,
        ?string $postalCode = null,
        ?string $country = null,
        bool $fromEvent = false,
        ?UuidInterface $uuid = null
    ) {
        $this->email = $email;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->fromEvent = $fromEvent;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString()
    {
        return $this->email ?: '';
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email = null): void
    {
        $this->email = $email;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode = null): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getCountryName(): ?string
    {
        return $this->country ? Countries::getName($this->country) : null;
    }

    public function isFromEvent(): bool
    {
        return $this->fromEvent;
    }

    public function setFromEvent(bool $fromEvent): bool
    {
        return $this->fromEvent = $fromEvent;
    }

    public function getConfirmedAt(): ?\DateTimeInterface
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?\DateTimeInterface $confirmedAt = null): void
    {
        $this->confirmedAt = $confirmedAt;
    }

    public function isConfirmed(): bool
    {
        return null !== $this->confirmedAt;
    }

    public function getToken(): ?UuidInterface
    {
        return $this->token;
    }

    public function setToken(?UuidInterface $token = null): void
    {
        $this->token = $token;
    }

    public function isPersonalDataCollection(): bool
    {
        return $this->personalDataCollection;
    }

    public function setPersonalDataCollection(bool $personalDataCollection): void
    {
        $this->personalDataCollection = $personalDataCollection;
    }
}
