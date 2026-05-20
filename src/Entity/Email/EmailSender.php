<?php

declare(strict_types=1);

namespace App\Entity\Email;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class EmailSender implements \Stringable, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $name = null;

    #[Assert\Email]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $email = null;

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::v4();
    }

    public function __toString(): string
    {
        return \sprintf('%s <%s>', $this->name, $this->email);
    }
}
