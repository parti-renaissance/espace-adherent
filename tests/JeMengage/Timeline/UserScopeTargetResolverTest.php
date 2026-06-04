<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline;

use App\Entity\Adherent;
use App\Entity\AdherentZoneBasedRole;
use App\Entity\MyTeam\DelegatedAccess;
use App\JeMengage\Timeline\UserScopeTargetResolver;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\TestCase;

final class UserScopeTargetResolverTest extends TestCase
{
    public function testResolveCollectsDirectAndDelegatedRolesDeduplicated(): void
    {
        $zoneRole = $this->createStub(AdherentZoneBasedRole::class);
        $zoneRole->method('getType')->willReturn('president_departmental_assembly');

        $delegated = $this->createStub(DelegatedAccess::class);
        $delegated->method('getType')->willReturn('referent');
        $delegated->roleCode = 'communication';

        $user = $this->createStub(Adherent::class);
        $user->method('getZoneBasedRoles')->willReturn([$zoneRole]);
        $user->method('isAnimator')->willReturn(true);
        $user->method('isPresidentOfAgora')->willReturn(false);
        $user->method('isGeneralSecretaryOfAgora')->willReturn(false);
        $user->method('hasNationalRole')->willReturn(true);
        $user->method('getReceivedDelegatedAccesses')->willReturn([$delegated]);

        $keys = new UserScopeTargetResolver()->resolve($user);

        self::assertSame([
            'president_departmental_assembly',
            ScopeEnum::ANIMATOR,
            ScopeEnum::NATIONAL,
            'referent:communication',
            'referent:*',
        ], $keys);
    }

    public function testResolveReturnsEmptyForUserWithoutRoles(): void
    {
        $user = $this->createStub(Adherent::class);
        $user->method('getZoneBasedRoles')->willReturn([]);
        $user->method('isAnimator')->willReturn(false);
        $user->method('isPresidentOfAgora')->willReturn(false);
        $user->method('isGeneralSecretaryOfAgora')->willReturn(false);
        $user->method('hasNationalRole')->willReturn(false);
        $user->method('getReceivedDelegatedAccesses')->willReturn([]);

        self::assertSame([], new UserScopeTargetResolver()->resolve($user));
    }
}
