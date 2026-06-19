<?php

declare(strict_types=1);

namespace App\Entity\Email;

use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\UnlayerJsonContentTrait;
use App\Mailer\Command\UpdateTransactionalEmailTemplateCommand;
use App\Repository\Email\TransactionalEmailTemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionalEmailTemplateRepository::class)]
#[UniqueEntity(fields: ['identifier'])]
class TransactionalEmailTemplate implements \Stringable, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use UnlayerJsonContentTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true, nullable: true)]
    public ?string $identifier = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(nullable: true)]
    public ?string $subject = null;

    #[Assert\NotBlank(groups: ['email_content'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $content = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: self::class)]
    public ?self $parent = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: EmailSender::class)]
    public ?EmailSender $sender = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isSync = false;

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::v4();
    }

    public function __toString(): string
    {
        return (string) ($this->identifier ?? $this->subject);
    }

    public function __clone()
    {
        $this->id = null;
        $this->uuid = Uuid::v4();
        $this->identifier .= '-copy';
    }

    public function getMessageClass(): string
    {
        $parts = explode('\\', (string) $this->identifier);

        return end($parts);
    }

    /**
     * Resolves the sender to use for this template: its own sender if set,
     * otherwise the parent's sender (inheritance), otherwise null (system fallback).
     */
    public function getEffectiveSender(): ?EmailSender
    {
        return $this->sender ?? $this->parent?->sender;
    }

    public function updateFrom(UpdateTransactionalEmailTemplateCommand $command): void
    {
        $this->identifier = $command->identifier;
        $this->subject = $command->subject;
        $this->content = $command->content;
        $this->jsonContent = $command->jsonContent;
        $this->parent = $command->parentObject;
        $this->isSync = true;
    }
}
