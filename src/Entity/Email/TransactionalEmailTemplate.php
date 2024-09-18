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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionalEmailTemplateRepository::class)]
#[UniqueEntity(fields: ['identifier'])]
class TransactionalEmailTemplate implements EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use UnlayerJsonContentTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\Length(max: '255')]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    public ?string $identifier = null;

    #[Assert\Length(max: '255')]
    #[ORM\Column(nullable: true)]
    public ?string $subject = null;

    #[Assert\NotBlank(groups: ['email_content'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $content = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
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

    public function __clone()
    {
        $this->id = null;
        $this->uuid = Uuid::uuid4();
        $this->identifier .= '-copy';
    }

    public function updateFrom(TransactionalEmailTemplate $template): void
    {
        $this->identifier = $template->identifier;
        $this->subject = $template->subject;
        $this->content = $template->content;
        $this->jsonContent = $template->jsonContent;
        $this->parent = $template->parent;
    }
}
