<?php

namespace AppBundle\Entity\AdherentMessage;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AuthoredInterface;
use Ramsey\Uuid\UuidInterface;

interface AdherentMessageInterface extends AuthoredInterface
{
    public function getLabel(): ?string;

    public function getUuid(): UuidInterface;

    public function getType(): string;

    public function getSubject(): ?string;

    public function getContent(): ?string;

    public function getFilter(): ?AdherentMessageFilterInterface;

    public function setFilter(AdherentMessageFilterInterface $filter): void;

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

    /** @return MailchimpCampaign[] */
    public function getMailchimpCampaigns(): array;

    public function addMailchimpCampaign(MailchimpCampaign $campaign): void;

    public function setMailchimpCampaigns(array $campaigns): void;

    public function resetFilter(): void;
}
