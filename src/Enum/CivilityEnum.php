<?php

namespace App\Enum;

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
}
