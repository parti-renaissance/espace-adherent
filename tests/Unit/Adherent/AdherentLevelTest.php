<?php

declare(strict_types=1);

namespace Tests\App\Unit\Adherent;

use App\Adherent\AdherentLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdherentLevelTest extends TestCase
{
    #[DataProvider('provideRoles')]
    public function testRoleMapping(AdherentLevel $level, string $expectedRole): void
    {
        self::assertSame($expectedRole, $level->role());
    }

    public static function provideRoles(): iterable
    {
        yield 'contact maps to the auth floor' => [AdherentLevel::CONTACT, 'ROLE_USER'];
        yield 'user maps to the auth floor' => [AdherentLevel::USER, 'ROLE_USER'];
        yield 'membre' => [AdherentLevel::MEMBRE, 'ROLE_MEMBRE'];
        yield 'adherent' => [AdherentLevel::ADHERENT, 'ROLE_ADHERENT'];
        yield 'adherent a jour' => [AdherentLevel::ADHERENT_A_JOUR, 'ROLE_ADHERENT_A_JOUR'];
    }
}
