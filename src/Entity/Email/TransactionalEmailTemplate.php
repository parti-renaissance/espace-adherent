<?php

namespace App\Entity\Email;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\UnlayerJsonContentTrait;
use App\Repository\Email\TransactionalEmailTemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionalEmailTemplateRepository::class)]
class TransactionalEmailTemplate implements EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use UnlayerJsonContentTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\Length(max: '255')]
    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $identifier = null;

    #[Assert\Length(max: '255')]
    #[ORM\Column(nullable: true)]
    public ?string $subject = null;

    #[Assert\NotBlank(groups: ['email_content'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $content = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    public ?self $parent = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return (string) $this->identifier;
    }
}
