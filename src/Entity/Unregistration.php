<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="unregistrations")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UnregistrationRepositry")
 */
class Unregistration
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $uuid;

    /**
     * @var string|null
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $postalCode;

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
        UuidInterface $uuid,
        string $postalCode,
        array $reasons,
        string $comment,
        \DateTime $registeredAt
    ) {
        $this->uuid = $uuid;
        $this->postalCode = $postalCode;
        $this->reasons = $reasons;
        $this->comment = $comment;
        $this->registeredAt = $registeredAt;
        $this->unregisteredAt = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
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

    public function getRegisteredAt(): ?\DateTime
    {
        return $this->registeredAt;
    }

    public function getUnregisteredAt(): ?\DateTime
    {
        return $this->unregisteredAt;
    }
}
