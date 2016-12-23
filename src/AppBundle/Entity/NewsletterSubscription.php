<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * NewsletterSubscription.
 *
 * @UniqueEntity(fields={"email"}, message="Cet email est déjà enregistré !")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="newsletter_subscription")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsletterSubscriptionRepository")
 */
class NewsletterSubscription
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email(
     *      message = "L'email '{{ value }}' n'est pas valide.",
     *      checkMX = true
     *)
     */
    private $email = '';

    /**
     * @ORM\PrePersist
     */
    public function setIdFromEmail()
    {
        $this->id = Uuid::uuid5(Uuid::NAMESPACE_URL, $this->email);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }
}
