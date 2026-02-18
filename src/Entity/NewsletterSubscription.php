<?php

declare(strict_types=1);

namespace App\Entity;

use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Repository\NewsletterSubscriptionRepository;
use App\Validator\Recaptcha as AssertRecaptcha;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Validator\Constraints as Assert;

#[AssertRecaptcha(groups: ['Subscription'])]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity(repositoryClass: NewsletterSubscriptionRepository::class)]
#[ORM\Table(name: 'newsletter_subscriptions')]
#[UniqueEntity(fields: ['email'], message: 'newsletter.already_registered')]
class NewsletterSubscription implements \Stringable, NewsletterSubscriptionInterface, EntitySoftDeletedInterface, RecaptchaChallengeInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use RecaptchaChallengeTrait;

    /**
     * @var string
     */
    #[Assert\Email(message: 'newsletter.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Assert\NotBlank(message: 'newsletter.email.not_blank')]
    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private $email;

    /**
     * @var string
     */
    #[Assert\Length(min: 2, max: 11, minMessage: 'newsletter.postalCode.invalid', maxMessage: 'newsletter.postalCode.invalid')]
    #[ORM\Column(type: 'string', length: 11, nullable: true)]
    private $postalCode;

    /**
     * The address country code (ISO2).
     *
     * @var string
     */
    #[ORM\Column(length: 2, nullable: true)]
    private $country;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $fromEvent;

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $confirmedAt;

    /**
     * @var UuidInterface
     */
    #[ORM\Column(type: 'uuid', unique: true, nullable: true)]
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
        ?UuidInterface $uuid = null,
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
        try {
            return $this->country ? Countries::getName($this->country) : null;
        } catch (MissingResourceException $e) {
        }

        return null;
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
