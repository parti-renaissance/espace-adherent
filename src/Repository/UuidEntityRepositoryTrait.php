<?php

namespace AppBundle\Repository;

use AppBundle\Exception\InvalidUuidException;
use Ramsey\Uuid\Uuid;

trait UuidEntityRepositoryTrait
{
    /**
     * Finds an entity by its unique UUID.
     *
     * @param string $uuid
     *
     * @return object|null
     *
     * @throws InvalidUuidException
     */
    public function findOneByUuid(string $uuid)
    {
        self::validUuid($uuid);

        return $this->findOneBy(['uuid' => $uuid]);
    }

    /**
     * Finds entities by their unique UUIDs.
     *
     * @param string[] $uuids
     *
     * @return object[]
     *
     * @throws InvalidUuidException
     */
    public function findByUuid(array $uuids): array
    {
        self::validUuids($uuids);

        return $this->findBy(['uuid' => $uuids]);
    }

    protected static function validUuid(string $uuid): void
    {
        if (false === Uuid::isValid($uuid)) {
            throw new InvalidUuidException(sprintf('Uuid "%s" is not valid.', $uuid));
        }
    }

    private static function validUuids(array $uuids): void
    {
        foreach ($uuids as $uuid) {
            self::validUuid($uuid);
        }
    }
}
