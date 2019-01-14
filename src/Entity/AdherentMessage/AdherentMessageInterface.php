<?php

namespace AppBundle\Entity\AdherentMessage;

use AppBundle\AdherentMessage\Filter\FilterDataObjectInterface;
use AppBundle\Entity\AuthoredInterface;
use Ramsey\Uuid\UuidInterface;

interface AdherentMessageInterface extends AuthoredInterface
{
    public function getLabel(): ?string;

    public function getExternalId(): ?string;

    public function setExternalId(string $id): void;

    public function getUuid(): UuidInterface;

    public function isSynchronized(): bool;

    public function setSynchronized(bool $value): void;

    public function getType(): string;

    public function getSubject(): ?string;

    public function getContent(): ?string;

    public function getFilter(): ?FilterDataObjectInterface;
}
