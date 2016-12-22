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
    private $email;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string
     */
    public function setIdFromEmail($email)
    {
        $this->id = Uuid::uuid5(Uuid::NAMESPACE_URL, $email);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
}
