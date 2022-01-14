<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QrCodeRepository")
 *
 * @UniqueEntity("name")
 */
class QrCode
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     * @Assert\Url
     */
    private $redirectUrl;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $count;

    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $createdBy;

    public function __construct(
        UuidInterface $uuid = null,
        string $name = null,
        string $redirectUrl = null,
        int $count = 0
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;
        $this->redirectUrl = $redirectUrl;
        $this->count = $count;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function increment(): void
    {
        ++$this->count;
    }

    public function getCreatedBy(): ?Administrator
    {
        return $this->createdBy;
    }

    public function setCreatedBy(Administrator $administrator): void
    {
        $this->createdBy = $administrator;
    }
}
