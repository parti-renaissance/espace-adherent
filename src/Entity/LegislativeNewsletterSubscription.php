<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Entity\Geo\Zone;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Repository\LegislativeNewsletterSubscriptionRepository;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\ZoneType as AssertZoneType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/legislative_newsletter_subscriptions',
            normalizationContext: ['groups' => ['legislative_newsletter_subscriptions_read'], 'iri' => true],
            denormalizationContext: ['groups' => ['legislative_newsletter_subscriptions_write']],
            validationContext: ['groups' => ['Default', 'legislative_newsletter_subscriptions_write']]
        ),
    ]
)]
#[AssertRecaptcha(groups: ['legislative_newsletter_subscriptions_write'])]
#[ORM\Entity(repositoryClass: LegislativeNewsletterSubscriptionRepository::class)]
#[ORM\Table]
#[UniqueEntity(fields: ['emailAddress'], message: 'legislative_newsletter.already_registered')]
class LegislativeNewsletterSubscription implements RecaptchaChallengeInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use RecaptchaChallengeTrait;

    #[Assert\Length(max: 255)]
    #[Groups(['legislative_newsletter_subscriptions_write'])]
    #[ORM\Column(nullable: true)]
    private ?string $firstName = null;

    #[Assert\Email(message: 'newsletter.email.invalid')]
    #[Assert\Length(max: 255, maxMessage: 'common.email.max_length')]
    #[Assert\NotBlank(message: 'newsletter.email.not_blank')]
    #[Groups(['legislative_newsletter_subscriptions_write'])]
    #[ORM\Column(unique: true)]
    private ?string $emailAddress = null;

    #[Assert\Length(min: 2, max: 11, minMessage: 'newsletter.postalCode.invalid', maxMessage: 'newsletter.postalCode.invalid')]
    #[Assert\NotBlank]
    #[Groups(['legislative_newsletter_subscriptions_write'])]
    #[ORM\Column(type: 'string', length: 11)]
    private ?string $postalCode = null;

    /**
     * @var Collection|Zone[]
     */
    #[AssertZoneType(types: ['district', 'foreign_district'])]
    #[ORM\ManyToMany(targetEntity: Zone::class)]
    private Collection $fromZones;

    #[Assert\IsTrue(message: 'common.personal_data_collection.required')]
    #[Groups(['legislative_newsletter_subscriptions_write'])]
    private bool $personalDataCollection = false;

    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $token;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $confirmedAt = null;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $emailAddress = null,
        ?string $postalCode = null,
        ?bool $personalDataCollection = null,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->emailAddress = $emailAddress;
        $this->postalCode = $postalCode;
        $this->personalDataCollection = (bool) $personalDataCollection;
        $this->token = Uuid::uuid4();
        $this->fromZones = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->emailAddress ?: '';
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return Collection|Zone[]
     */
    public function getFromZones(): Collection
    {
        return $this->fromZones;
    }

    public function setFromZones(array $fromZones): void
    {
        array_walk($fromZones, [$this, 'addFromZone']);
    }

    public function addFromZone(Zone $fromZone): void
    {
        if (!$this->fromZones->contains($fromZone)) {
            $this->fromZones->add($fromZone);
        }
    }

    public function removeFromZone(Zone $fromZone): void
    {
        $this->fromZones->removeElement($fromZone);
    }

    public function isPersonalDataCollection(): bool
    {
        return $this->personalDataCollection;
    }

    public function setPersonalDataCollection(bool $personalDataCollection): void
    {
        $this->personalDataCollection = $personalDataCollection;
    }

    public function getToken(): UuidInterface
    {
        return $this->token;
    }

    public function setToken(?UuidInterface $token = null): void
    {
        $this->token = $token;
    }

    public function getConfirmedAt(): ?\DateTimeInterface
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?\DateTimeInterface $confirmedAt = null): void
    {
        $this->confirmedAt = $confirmedAt;
    }
}
