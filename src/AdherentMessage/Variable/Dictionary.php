<?php

declare(strict_types=1);

namespace App\AdherentMessage\Variable;

use App\Entity\Adherent;
use MyCLabs\Enum\Enum;

class Dictionary extends Enum
{
    public const SALUTATION = 'Chère/Cher Prénom';
    public const FIRST_NAME = 'Prénom';
    public const LAST_NAME = 'Nom';
    public const PUBLIC_ID = 'Numéro militant';

    public static function getList(): array
    {
        $result = [];
        foreach (static::getConfig() as $row) {
            $result[] = [
                'label' => trim($row['code'], '{}'),
                'code' => $row['code'],
                'description' => $row['description'],
            ];
        }

        return $result;
    }

    public static function getConfig(): array
    {
        return [
            [
                'code' => static::makeCode(self::SALUTATION),
                'description' => null,
                'value' => fn (Adherent $adherent) => \sprintf('%s %s', $adherent->isFemale() ? 'Chère' : 'Cher', $adherent->getFirstName()),
            ],
            [
                'code' => static::makeCode(self::FIRST_NAME),
                'description' => null,
                'value' => fn (Adherent $adherent) => $adherent->getFirstName(),
            ],
            [
                'code' => static::makeCode(self::LAST_NAME),
                'description' => null,
                'value' => fn (Adherent $adherent) => $adherent->getLastName(),
            ],
            [
                'code' => static::makeCode(self::PUBLIC_ID),
                'description' => null,
                'value' => fn (Adherent $adherent) => $adherent->getPublicId(),
            ],
        ];
    }

    private static function makeCode(string $name): string
    {
        return \sprintf('{{%s}}', $name);
    }
}
