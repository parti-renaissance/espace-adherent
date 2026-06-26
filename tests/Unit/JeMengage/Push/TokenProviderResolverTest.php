<?php

declare(strict_types=1);

namespace Tests\App\Unit\JeMengage\Push;

use App\Collection\ZoneCollection;
use App\Entity\Action\Action;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Firebase\Notification\MulticastNotificationInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\JeMengage\Push\NotificationScope;
use App\JeMengage\Push\TokenProviderResolver;
use App\Repository\PushTokenRepository;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\TestCase;

class TokenProviderResolverTest extends TestCase
{
    public function testMilitantEventResolvesCityZoneTokensWithoutClimbingToAssembly(): void
    {
        $cityZone = new Zone(Zone::CITY, '75056', 'Paris');

        $event = $this->createStub(Event::class);
        $event->method('getAuthorScope')->willReturn(ScopeEnum::MILITANT);
        $event->method('getZones')->willReturn(new ZoneCollection([$cityZone]));

        $notification = $this->createStub(MulticastNotificationInterface::class);
        $notification->method('getScope')->willReturn(NotificationScope::zone('75056'));

        $repository = $this->createMock(PushTokenRepository::class);
        $repository
            ->expects(self::once())
            ->method('findAllForZone')
            ->with($cityZone)
            ->willReturn(['token-paris'])
        ;

        $resolver = new TokenProviderResolver($repository);
        $tokens = $resolver->getTokens($notification, $event, $this->createStub(SendNotificationCommandInterface::class));

        self::assertSame(['token-paris'], $tokens);
    }

    public function testActionResolvesCityZoneTokensInsteadOfAssembly(): void
    {
        $cityZone = new Zone(Zone::CITY, '75056', 'Paris');
        $departmentZone = new Zone(Zone::DEPARTMENT, '75', 'Paris');

        // getZones() also holds the parent department; the resolver must pick the commune zone, not the first one.
        $action = $this->createStub(Action::class);
        $action->method('getZones')->willReturn(new ZoneCollection([$departmentZone, $cityZone]));
        $action->method('getCityOrBoroughZones')->willReturn([$cityZone]);

        $notification = $this->createStub(MulticastNotificationInterface::class);
        $notification->method('getScope')->willReturn(NotificationScope::zone('75056'));

        $repository = $this->createMock(PushTokenRepository::class);
        $repository
            ->expects(self::once())
            ->method('findAllForZone')
            ->with($cityZone)
            ->willReturn(['token-paris-action'])
        ;

        $resolver = new TokenProviderResolver($repository);
        $tokens = $resolver->getTokens($notification, $action, $this->createStub(SendNotificationCommandInterface::class));

        self::assertSame(['token-paris-action'], $tokens);
    }

    public function testActionResolvesBoroughZoneTokens(): void
    {
        $boroughZone = new Zone(Zone::BOROUGH, '75108', 'Paris 8e');
        $departmentZone = new Zone(Zone::DEPARTMENT, '75', 'Paris');

        // A Paris/Lyon/Marseille action is attached to an arrondissement (borough), targeted directly.
        $action = $this->createStub(Action::class);
        $action->method('getZones')->willReturn(new ZoneCollection([$departmentZone, $boroughZone]));
        $action->method('getCityOrBoroughZones')->willReturn([$boroughZone]);

        $notification = $this->createStub(MulticastNotificationInterface::class);
        $notification->method('getScope')->willReturn(NotificationScope::zone('75108'));

        $repository = $this->createMock(PushTokenRepository::class);
        $repository
            ->expects(self::once())
            ->method('findAllForZone')
            ->with($boroughZone)
            ->willReturn(['token-paris-8e-action'])
        ;

        $resolver = new TokenProviderResolver($repository);
        $tokens = $resolver->getTokens($notification, $action, $this->createStub(SendNotificationCommandInterface::class));

        self::assertSame(['token-paris-8e-action'], $tokens);
    }
}
