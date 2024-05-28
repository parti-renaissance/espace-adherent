<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Geo\Zone;
use App\Recaptcha\RecaptchaChallengeInterface;
use App\Recaptcha\RecaptchaChallengeTrait;
use App\Validator\Recaptcha as AssertRecaptcha;
use App\Validator\ZoneType as AssertZoneType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as AssertUniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "post": {
 *             "path": "/legislative_newsletter_subscriptions",
 *             "denormalization_context": {
 *                 "groups": {"legislative_newsletter_subscriptions_write"},
 *             },
 *             "normalization_context": {
 *                 "groups": {"legislative_newsletter_subscriptions_read"},
 *                 "iri": true,
 *             },
 *             "validation_groups": {"Default", "legislative_newsletter_subscriptions_write"},
 *         }
 *     },
 *     itemOperations={},
 * )
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="App\Repository\LegislativeNewsletterSubscriptionRepository")
 *
 * @AssertUniqueEntity(fields={"emailAddress"}, message="legislative_newsletter.already_registered")
 * @AssertRecaptcha(api="friendly_captcha", groups={"legislative_newsletter_subscriptions_write"})
 */
class LegislativeNewsletterSubscription implements RecaptchaChallengeInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use RecaptchaChallengeTrait;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     */
    #[Groups(['legislative_newsletter_subscriptions_write'])]
    private ?string $firstName = null;

    /**
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank(message="newsletter.email.not_blank")
     * @Assert\Email(message="newsletter.email.invalid")
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    #[Groups(['legislative_newsletter_subscriptions_write'])]
    private ?string $emailAddress = null;

    /**
     * @ORM\Column(type="string", length=11)
     *
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=11,
     *     minMessage="newsletter.postalCode.invalid",
     *     maxMessage="newsletter.postalCode.invalid"
     * )
     */
    #[Groups(['legislative_newsletter_subscriptions_write'])]
    private ?string $postalCode = null;

    /**
     * @var Collection|Zone[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone")
     *
     * @AssertZoneType(types={"district", "foreign_district"})
     */
    private Collection $fromZones;

    /**
     * @Assert\IsTrue(message="common.personal_data_collection.required")
     */
    #[Groups(['legislative_newsletter_subscriptions_write'])]
    private bool $personalDataCollection = false;

    /**
     * @ORM\Column(type="uuid", unique=true)
     */
    private UuidInterface $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $confirmedAt = null;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $emailAddress = null,
        ?string $postalCode = null,
        ?bool $personalDataCollection = null
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
