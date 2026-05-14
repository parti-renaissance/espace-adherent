<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Bridge\Doctrine\Types\AbstractUidType;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

class UuidType extends AbstractUidType
{
    public const NAME = 'uuid';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['fixed' => true, 'length' => 36]);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof AbstractUid) {
            return $value->toRfc4122();
        }

        if (null === $value || '' === $value) {
            return null;
        }

        if (!\is_string($value)) {
            throw new \InvalidArgumentException(\sprintf('Expected %s or string, got "%s".', AbstractUid::class, get_debug_type($value)));
        }

        return Uuid::fromString($value)->toRfc4122();
    }

    protected function getUidClass(): string
    {
        return Uuid::class;
    }
}
