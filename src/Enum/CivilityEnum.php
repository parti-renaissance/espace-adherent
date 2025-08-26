<?php

namespace App\Enum;

use App\ValueObject\Genders;

enum CivilityEnum: string
{
    case Madame = 'Madame';
    case Monsieur = 'Monsieur';

    public static function getAsArray(): array
    {
        return array_reduce(
            self::cases(),
            static fn (array $choices, self $type) => $choices + [$type->name => $type->value],
            [],
        );
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function fromGender(?string $gender): ?self
    {
        return match ($gender) {
            Genders::FEMALE => self::Madame,
            Genders::MALE => self::Monsieur,
            default => null,
        };
    }
}
