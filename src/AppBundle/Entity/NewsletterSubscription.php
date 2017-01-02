<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(fields={"email"}, message="newsletter.email.not_unique")
 * @ORM\Table(name="newsletter_subscriptions")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsletterSubscriptionRepository")
 */
class NewsletterSubscription
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\Email(message="newsletter.email.invalid")
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $clientIp;

    /**
     * @return string|null
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    public function setClientIp(string $clientIp): NewsletterSubscription
    {
        $this->clientIp = $clientIp;

        return $this;
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
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email): NewsletterSubscription
    {
        $this->email = $email;

        return $this;
    }

    public static function createUuid(string $email): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_URL, $email);
    }
}
