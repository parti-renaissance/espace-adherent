<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * NewsletterSubscription
 *
 * @ORM\Table(name="newsletter_subscription")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsletterSubscriptionRepository")
 */
class NewsletterSubscription
{
    /**
    * @var UuidInterface
    *
    * @Id
    * @Column(type="uuid")
    * @GeneratedValue(strategy="NONE")
    */
    private $id;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\Email(
     *      message = "The email '{{ value }}' is not a valid email.",
     *      checkMX = true
     *)
     */
    private $email;

    public function __construct($email)
    {
       $this->id = Uuid::uuid5(Uuid::NAMESPACE_URL, $email);
       $this->email = $email;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}
