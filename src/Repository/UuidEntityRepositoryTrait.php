<?php

namespace App\Repository;

use App\Exception\InvalidUuidException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait UuidEntityRepositoryTrait
{
    /**
     * Finds an entity by its unique UUID even if the object has 'enabled' property set to 'true'
     * due to deactivation of 'enabled' Doctrine filter.
     *
     * @throws InvalidUuidException
     */
    public function findOneByUuid(UuidInterface|string $uuid): ?object
    {
        if (\is_string($uuid)) {
            static::validUuid($uuid);
        }

        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function findByUuid(array $uuids): array
    {
        self::validUuids($uuids);

        return $this->findBy(['uuid' => $uuids]);
    }

    protected static function validUuid(string $uuid): void
    {
        if (false === Uuid::isValid($uuid)) {
            throw new InvalidUuidException(\sprintf('Uuid "%s" is not valid.', $uuid));
        }
    }

    private static function validUuids(array $uuids): void
    {
        foreach ($uuids as $uuid) {
            self::validUuid($uuid);
        }
    }
}
