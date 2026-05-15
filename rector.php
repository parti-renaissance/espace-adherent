<?php

declare(strict_types=1);

/*
 * Migration ramsey/uuid → symfony/uid.
 *
 * Run:
 *   docker compose exec app composer require --dev rector/rector
 *   docker compose exec app ./vendor/bin/rector process --dry-run
 *   docker compose exec app ./vendor/bin/rector process
 *
 * What this config handles automatically:
 *   - use Ramsey\Uuid\Uuid                                  → use Symfony\Component\Uid\Uuid
 *   - use Ramsey\Uuid\UuidInterface                         → use Symfony\Component\Uid\Uuid
 *   - use Ramsey\Uuid\Doctrine\UuidGenerator                → use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator
 *   - use Ramsey\Uuid\Doctrine\UuidType                     → use Symfony\Bridge\Doctrine\Types\UuidType
 *   - use Ramsey\Uuid\Exception\InvalidUuidStringException  → use InvalidArgumentException (Symfony/Uid throws \InvalidArgumentException)
 *   - Uuid::uuid1/3/4/5/6/7()                               → Uuid::v1/v3/v4/v5/v6/v7()
 *   - $uuid->toString()                                     → $uuid->toRfc4122()
 *
 * Manual steps required AFTER Rector (Rector does not handle these):
 *   1. Uuid::NAMESPACE_OID (8 occurrences) — Symfony\Uid has no NAMESPACE_* constants.
 *      Replace with Uuid::fromString('6ba7b812-9dad-11d1-80b4-00c04fd430c8') or expose
 *      a project-level constant. Locations:
 *        - src/OAuth/PersistentTokenFactory.php
 *        - src/Entity/Committee.php, Adherent.php, CommitteeMembership.php, FacebookProfile.php
 *        - src/DataFixtures/ORM/LoadOAuthTokenData.php (×3)
 *
 *   2. composer.json packages to remove (after Rector + green CI):
 *        composer remove ramsey/uuid ramsey/uuid-doctrine api-platform/ramsey-uuid
 *
 *   3. Audit tests/ for mocks targeting Ramsey\Uuid\UuidInterface — Symfony\Uid\Uuid is a
 *      concrete class, not an interface. Replace ->createMock(UuidInterface::class) by
 *      Uuid::v4() or a real Uuid instance.
 *
 *   4. Verify API Platform serialization end-to-end: routes /api/.../{uuid} and JSON
 *      payloads must keep the canonical RFC 4122 string format. The api-platform/ramsey-uuid
 *      bundle is replaced by Symfony\Uid native support in API Platform ≥ 3.
 */

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSkip([
        __DIR__.'/migrations',
        __DIR__.'/var',
        __DIR__.'/vendor',
        __DIR__.'/node_modules',
        __DIR__.'/dump',
        __DIR__.'/app',
    ])
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withConfiguredRule(RenameClassRector::class, [
        'Ramsey\Uuid\Uuid' => 'Symfony\Component\Uid\Uuid',
        'Ramsey\Uuid\UuidInterface' => 'Symfony\Component\Uid\Uuid',
        'Ramsey\Uuid\Doctrine\UuidGenerator' => 'Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator',
        'Ramsey\Uuid\Doctrine\UuidType' => 'Symfony\Bridge\Doctrine\Types\UuidType',
        'Ramsey\Uuid\Exception\InvalidUuidStringException' => \InvalidArgumentException::class,
    ])
    ->withConfiguredRule(RenameStaticMethodRector::class, [
        new RenameStaticMethod('Symfony\Component\Uid\Uuid', 'uuid1', 'Symfony\Component\Uid\Uuid', 'v1'),
        new RenameStaticMethod('Symfony\Component\Uid\Uuid', 'uuid3', 'Symfony\Component\Uid\Uuid', 'v3'),
        new RenameStaticMethod('Symfony\Component\Uid\Uuid', 'uuid4', 'Symfony\Component\Uid\Uuid', 'v4'),
        new RenameStaticMethod('Symfony\Component\Uid\Uuid', 'uuid5', 'Symfony\Component\Uid\Uuid', 'v5'),
        new RenameStaticMethod('Symfony\Component\Uid\Uuid', 'uuid6', 'Symfony\Component\Uid\Uuid', 'v6'),
        new RenameStaticMethod('Symfony\Component\Uid\Uuid', 'uuid7', 'Symfony\Component\Uid\Uuid', 'v7'),
        new RenameStaticMethod('Symfony\Component\Uid\Uuid', 'isValid', 'App\Utils\UuidUtils', 'isValid'),
    ])
    ->withConfiguredRule(RenameMethodRector::class, [
        new MethodCallRename('Symfony\Component\Uid\Uuid', 'toString', 'toRfc4122'),
    ]);
