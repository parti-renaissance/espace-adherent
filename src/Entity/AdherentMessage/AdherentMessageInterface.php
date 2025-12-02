<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\Entity\IndexableEntityInterface;
use App\Scope\Scope;
use Ramsey\Uuid\UuidInterface;

interface AdherentMessageInterface extends AuthorInstanceInterface, IndexableEntityInterface
{
    public const SOURCE_CADRE = 'cadre';
    public const SOURCE_VOX = 'vox';

    public function getLabel(): ?string;

    public function getUuid(): UuidInterface;

    public function getInstanceScope(): ?string;

    public function getSubject(): ?string;

    public function getContent(): ?string;

    public function getFilter(): ?AdherentMessageFilterInterface;

    public function setFilter(?AdherentMessageFilterInterface $filter): void;

    public function getFromName(): string;

    public function setLabel(string $label): void;

    public function setSubject(string $subject): void;

    public function setContent(string $content): void;

    public function getStatus(): string;

    public function isSent(): bool;

    public function getRecipientCount(): ?int;

    public function setRecipientCount(?int $recipientCount): void;

    public function getSentAt(): ?\DateTimeInterface;

    public function getId(): ?int;

    public function isSynchronized(): bool;

    public function setSynchronized(bool $value): void;

    /** @return MailchimpCampaign[] */
    public function getMailchimpCampaigns(): array;

    public function addMailchimpCampaign(MailchimpCampaign $campaign): void;

    public function setMailchimpCampaigns(array $campaigns): void;

    public function resetFilter(): void;

    public function markAsSent(): void;

    public static function createFromAdherent(Adherent $adherent, ?UuidInterface $uuid = null): self;

    public function getSource(): string;

    public function setSource(string $source): void;

    public function getSender(): ?Adherent;

    public function setSender(?Adherent $sender): void;

    public function updateSenderDataFromScope(Scope $scope): void;

    public function setInstanceScope(?string $instanceScope): void;

    public function isStatutory(): bool;
}
