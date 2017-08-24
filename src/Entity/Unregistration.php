<?php

namespace AppBundle\Entity;

use AppBundle\Geocoder\GeoPointInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="unregistrations")
 * @ORM\Entity
 */
class Unregistration implements GeoPointInterface
{
    use EntityPostAddressTrait;
    use EntityPersonNameTrait;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="json_array")
     * @Assert\NotBlank(message="adherent.unregistration.reasons")
     */
    private $reasons = [];

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=10, max=1000)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registeredAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $unregisteredAt;

    public function __construct(
        string $firstName,
        string $lastName,
        string $emailAddress,
        PostAddress $postAddress,
        array $reasons,
        string $comment,
        \DateTime $registeredAt
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->postAddress = $postAddress;
        $this->reasons = $reasons;
        $this->comment = $comment;
        $this->registeredAt = $registeredAt;
        $this->unregisteredAt = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function getReasonsAsJson(): string
    {
        return \GuzzleHttp\json_encode($this->reasons, JSON_PRETTY_PRINT);
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getPostAddress(): PostAddress
    {
        return $this->postAddress;
    }

    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    public function getUnregisteredAt(): ?\DateTime
    {
        return $this->unregisteredAt;
    }
}
