<?php

namespace AppBundle\Referent;

use libphonenumber\PhoneNumber;
use League\Flysystem\FilesystemInterface;

class ReferentDumpedDatabaseReader
{
    private $storage;

    public function __construct(FilesystemInterface $storage)
    {
        $this->storage = $storage;
    }

    public function readList(string $referentUuid, string $type): ?string
    {
        $type = strtolower($type);
        if (!in_array($type, ReferentDatabaseDumper::EXPORT_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('The "%s" export type is unsupported and must be one of %s.', $type, implode(', ', ReferentDatabaseDumper::EXPORT_TYPES)));
        }

        $filename = ReferentDatabaseDumper::STORAGE_SPACE.'/'.$referentUuid.'_'.$type.'.data';
        if (!$this->storage->has($filename)) {
            return null;
        }

        $content = $this->storage->read($filename);
        if (!$content) {
            return null;
        }

        return $content;
    }

    public function filterAllowedUsers(string $referentUuid, ?string $selected): array
    {
        if (!$selected) {
            return [];
        }

        $data = explode(',', $selected);
        $selectedUsers = [];

        foreach ($data as $row) {
            $row = explode('|', $row);

            if (count($row) !== 2 || !is_numeric($row[1]) || !in_array($row[0], ['a', 'n'], true)) {
                continue;
            }

            $selectedUsers[] = [
                'type' => $row[0],
                'id' => $row[1],
            ];
        }

        if (!$selectedUsers) {
            return [];
        }

        $allowedUsers = @unserialize($this->readList($referentUuid, 'serialized') ?? 'a:0:{}', [
            'allowed_classes' => [
                ManagedUser::class,
                PhoneNumber::class,
                \DateTime::class,
            ],
        ]);

        $selectedManagedUser = [];

        foreach ($selectedUsers as $user) {
            if (!isset($user['type'], $user['id'])) {
                continue;
            }

            $type = $user['type'] === 'a' ? ManagedUser::TYPE_ADHERENT : ManagedUser::TYPE_NEWSLETTER_SUBSCRIBER;

            if (!isset($allowedUsers[$type][(int) $user['id']])) {
                continue;
            }

            $selectedManagedUser[] = $allowedUsers[$type][(int) $user['id']];
        }

        return $selectedManagedUser;
    }

    public function serializeSelected(array $selected): string
    {
        $serialized = [];

        foreach ($selected as $user) {
            $serialized[] = ($user->getType() === ManagedUser::TYPE_ADHERENT ? 'a' : 'n').'|'.$user->getId();
        }

        return implode(',', $serialized);
    }
}
