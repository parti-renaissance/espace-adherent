<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageDataObject;
use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\Adherent;
use App\Entity\Audience\AudienceInterface;
use App\Entity\AuthoredItemsCollectionInterface;
use Ramsey\Uuid\UuidInterface;

interface AdherentMessageInterface extends AuthoredItemsCollectionInterface
{
    public const SOURCE_PLATFORM = 'platform';
    public const SOURCE_API = 'api';

    public function getLabel(): ?string;

    public function getUuid(): UuidInterface;

    public function getType(): string;

    public function getSubject(): ?string;

    public function getContent(): ?string;

    public function getFilter(): ?AdherentMessageFilterInterface;

    public function setFilter(?AdherentMessageFilterInterface $filter): void;

    public function getFromName(): ?string;

    public function hasReadOnlyFilter(): bool;

    public function setLabel(string $label): void;

    public function setSubject(string $subject): void;

    public function setContent(string $content): void;

    public function getStatus(): string;

    public function isSent(): bool;

    public function getRecipientCount(): ?int;

    public function getSentAt(): ?\DateTimeInterface;

    public function getId(): ?int;

    public function isSynchronized(): bool;

    public function setSynchronized(bool $value): void;

    /** @return MailchimpCampaign[] */
    public function getMailchimpCampaigns(): array;

    public function addMailchimpCampaign(MailchimpCampaign $campaign): void;

    public function setMailchimpCampaigns(array $campaigns): void;

    public function resetFilter(): void;

    public function updateFromDataObject(AdherentMessageDataObject $dataObject): AdherentMessageInterface;

    public function markAsSent(): void;

    public static function createFromAdherent(Adherent $adherent): self;

    public function getSource(): string;

    public function setSource(string $source): void;

    public function getAudience(): ?AudienceInterface;

    public function setAudience(AudienceInterface $audience): void;
}
