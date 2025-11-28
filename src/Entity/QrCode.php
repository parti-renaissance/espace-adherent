<?php

declare(strict_types=1);

namespace App\Entity;

use App\QrCode\QrCodeHostEnum;
use App\Repository\QrCodeRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QrCodeRepository::class)]
#[UniqueEntity(fields: ['name'])]
class QrCode
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    private ?string $name;

    #[Assert\NotBlank]
    #[Assert\Url]
    #[ORM\Column(type: 'text')]
    private ?string $redirectUrl;

    #[Assert\Choice(choices: QrCodeHostEnum::ALL)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private ?string $host;

    #[ORM\Column(type: 'integer')]
    private int $count;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private ?Administrator $createdBy = null;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $name = null,
        ?string $redirectUrl = null,
        ?string $host = null,
        int $count = 0,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;
        $this->redirectUrl = $redirectUrl;
        $this->host = $host;
        $this->count = $count;
    }

    public function __toString()
    {
        return (string) $this->name;
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

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): void
    {
        $this->host = $host;
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
