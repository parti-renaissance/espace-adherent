<?php

declare(strict_types=1);

namespace Tests\App\Unit\JeMengage\Push\Notification;

use App\Collection\ZoneCollection;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\JeMengage\Push\Notification\EventCreatedNotification;
use App\JeMengage\Push\NotificationScope;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\TestCase;

class EventCreatedNotificationTest extends TestCase
{
    public function testMilitantEventTargetsCityZoneAndName(): void
    {
        $cityZone = new Zone(Zone::CITY, '75056', 'Paris');

        $event = $this->createStub(Event::class);
        $event->method('getAuthorScope')->willReturn(ScopeEnum::MILITANT);
        $event->method('getZones')->willReturn(new ZoneCollection([$cityZone]));
        $event->method('isInvitation')->willReturn(false);
        $event->method('getCommittee')->willReturn(null);
        $event->method('isNational')->willReturn(false);
        $event->method('getName')->willReturn('Apéro militant');
        $event->method('getBeginAt')->willReturn(new \DateTime('2026-06-01 18:00:00'));
        $event->method('getInlineFormattedAddress')->willReturn('Paris');

        $notification = EventCreatedNotification::create($event);

        self::assertSame(NotificationScope::zone('75056'), $notification->getScope());
        self::assertSame('Paris, nouvel événement', $notification->getTitle());
    }
}
