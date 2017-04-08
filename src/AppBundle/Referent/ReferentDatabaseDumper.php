<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Exception\ReferentNotFoundException;
use AppBundle\Repository\AdherentRepository;
use League\Flysystem\FilesystemInterface;

class ReferentDatabaseDumper
{
    const STORAGE_SPACE = 'dumped_referents_users';

    const EXPORT_TYPES = [
        'all',
        'serialized',
        'subscribers',
        'adherents',
        'non_followers',
        'followers',
        'hosts',
    ];

    private $repository;
    private $factory;
    private $serializer;
    private $storage;

    public function __construct(
        AdherentRepository $repository,
        ManagedUserFactory $factory,
        ManagedUserExporter $serializer,
        FilesystemInterface $storage
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->serializer = $serializer;
        $this->storage = $storage;
    }

    public function dump(string $identifier, string $type = 'all'): void
    {
        $type = strtolower($type);
        if (!in_array($type, self::EXPORT_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('The "%s" export type is unsupported and must be one of %s.', $type, implode(', ', self::EXPORT_TYPES)));
        }

        if (!$referent = $this->repository->findReferent($identifier)) {
            throw new ReferentNotFoundException(sprintf('Unable to find referent adherent identified by "%s".', $identifier));
        }

        $this->export($referent->getUuid(), $type, $this->getManagedUsers($referent, $type));
    }

    private function getManagedUsers(Adherent $referent, string $type): array
    {
        if ('all' === $type) {
            return $this->factory->createManagedUsersCollectionFor($referent);
        }

        if ('serialized' === $type) {
            return $this->factory->createManagedUsersListIndexedByTypeAndId($referent);
        }

        if ('subscribers' === $type) {
            return $this->factory->createManagedSubscribersCollectionFor($referent);
        }

        if ('adherents' === $type) {
            return $this->factory->createManagedAdherentsCollectionFor($referent);
        }

        if ('non_followers' === $type) {
            return $this->factory->createManagedNonFollowersCollectionFor($referent);
        }

        if ('followers' === $type) {
            return $this->factory->createManagedFollowersCollectionFor($referent);
        }

        // 'hosts' === $type
        return $this->factory->createManagedHostsCollectionFor($referent);
    }

    private function export(string $uuid, string $type, array $users): void
    {
        $this->save(
            $this->getFilename($uuid, $type),
            $this->serialize($type, $users)
        );
    }

    private function getFilename(string $uuid, string $type): string
    {
        return sprintf('%s/%s_%s.data', self::STORAGE_SPACE, $uuid, $type);
    }

    private function serialize(string $type, array $users): string
    {
        if ('serialized' === $type) {
            return serialize($users);
        }

        return $this->serializer->exportAsJson($users);
    }

    private function save(string $path, string $contents): void
    {
        if (!$this->storage->has(self::STORAGE_SPACE)) {
            $this->storage->createDir(self::STORAGE_SPACE);
        }

        if (!$this->storage->put($path, $contents)) {
            throw new \RuntimeException(sprintf('Unable to save dumped referent database into "%s" file.', $path));
        }
    }
}
