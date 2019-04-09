<?php

namespace AppBundle\Mailchimp\Synchronisation\Command;

use Ramsey\Uuid\UuidInterface;

abstract class AbstractUpdateStaticSegmentCommand implements UpdateStaticSegmentCommandInterface
{
    private $adherentUuid;
    private $objectUuid;
    private $entityClass;

    public function __construct(UuidInterface $adherentUuid, UuidInterface $objectUuid, string $entityClass)
    {
        $this->adherentUuid = $adherentUuid;
        $this->objectUuid = $objectUuid;
        $this->entityClass = $entityClass;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    public function getObjectUuid(): UuidInterface
    {
        return $this->objectUuid;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
