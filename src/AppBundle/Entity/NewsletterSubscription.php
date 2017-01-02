<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as AssertUniqueEntity;

/**
 * @AssertUniqueEntity(fields={"email"}, message="neswletter.already_registered")
 *
 * @ORM\Table(name="newsletter_subscriptions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsletterSubscriptionRepository")
 */
class NewsletterSubscription
{
    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, unique=true)
     *
     * @Assert\NotBlank(message="neswletter.email.not_blank")
     * @Assert\Email(message="neswletter.email.invalid")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=11)
     *
     * @Assert\Length(min=2, max=11, minMessage="neswletter.postalCode.invalid", maxMessage="neswletter.postalCode.invalid")
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $clientIp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public static function createUuid(string $email): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, $email);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): NewsletterSubscription
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email): NewsletterSubscription
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): NewsletterSubscription
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @param string|null $clientIp
     *
     * @return $this
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): NewsletterSubscription
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
