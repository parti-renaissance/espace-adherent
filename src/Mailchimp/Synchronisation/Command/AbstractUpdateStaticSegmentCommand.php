<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Command;

use Symfony\Component\Uid\Uuid;

abstract class AbstractUpdateStaticSegmentCommand implements UpdateStaticSegmentCommandInterface
{
    private $adherentUuid;
    private $objectUuid;
    private $entityClass;

    public function __construct(Uuid $adherentUuid, Uuid $objectUuid, string $entityClass)
    {
        $this->adherentUuid = $adherentUuid;
        $this->objectUuid = $objectUuid;
        $this->entityClass = $entityClass;
    }

    public function getAdherentUuid(): Uuid
    {
        return $this->adherentUuid;
    }

    public function getObjectUuid(): Uuid
    {
        return $this->objectUuid;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
