<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as AssertUniqueEntity;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueEntity(fields={"email"}, message="neswletter.already_registered")
 *
 * @ORM\Table(name="newsletter_subscriptions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsletterSubscriptionRepository")
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @Algolia\Index(autoIndex=false)
 */
class NewsletterSubscription implements EntitySoftDeletedInterface
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, unique=true)
     *
     * @Assert\NotBlank(message="neswletter.email.not_blank")
     * @Assert\Email(message="neswletter.email.invalid")
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
     *     minMessage="neswletter.postalCode.invalid",
     *     maxMessage="neswletter.postalCode.invalid"
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

    public function __construct(
        string $email = null,
        string $postalCode = null,
        string $country = null,
        bool $fromEvent = false
    ) {
        $this->email = $email;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->fromEvent = $fromEvent;
    }

    public function __toString()
    {
        return $this->email ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email = null): void
    {
        $this->email = $email;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode = null): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
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
        return $this->country ? Intl::getRegionBundle()->getCountryName($this->country) : null;
    }

    public function isFromEvent(): bool
    {
        return $this->fromEvent;
    }
}
