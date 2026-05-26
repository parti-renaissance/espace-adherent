<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SignupSourceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SignupSourceRepository::class)]
class SignupSource
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Le code doit être alphanumérique (lettres, chiffres, underscore).')]
    #[ORM\Column(length: 100, unique: true)]
    public string $code;

    #[Assert\NotBlank]
    #[ORM\Column]
    public string $label;

    #[ORM\Column(options: ['default' => true])]
    public bool $enabled = true;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    public ?string $friendlyCaptchaSiteKey = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
