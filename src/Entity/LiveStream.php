<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class LiveStream implements Timestampable, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use TimestampableEntity;
    use EntityAdministratorBlameableTrait;

    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $title = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Url]
    #[ORM\Column]
    public ?string $url = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $beginAt = null;

    #[Assert\Expression('!value or value > this.beginAt', message: 'committee.event.invalid_date_range')]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $finishAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
