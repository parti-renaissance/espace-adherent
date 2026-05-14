<?php

declare(strict_types=1);

namespace App\Entity\Renaissance;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Renaissance\NewsletterSourceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NewsletterSourceRepository::class)]
#[ORM\Table(name: 'renaissance_newsletter_source')]
class NewsletterSource
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

    #[Assert\Url(protocols: ['https'])]
    #[ORM\Column(length: 500, nullable: true)]
    public ?string $confirmationRedirectUrl = null;

    #[ORM\Column(nullable: true)]
    public ?string $mailchimpTag = null;

    #[ORM\Column(options: ['default' => true])]
    public bool $enabled = true;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
    }

    public function __toString(): string
    {
        return $this->label ?? $this->code ?? '';
    }
}
