<?php

declare(strict_types=1);

namespace Tests\App\Unit\JeMengage\Push;

use App\Entity\Action\Action;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\TimelineItemPrivateMessage;
use App\JeMengage\Push\Notification\ActionBeginNotification;
use App\JeMengage\Push\Notification\ActionCancelledNotification;
use App\JeMengage\Push\Notification\ActionCreatedNotification;
use App\JeMengage\Push\Notification\ActionUpdatedNotification;
use App\JeMengage\Push\Notification\AdherentMessageSentNotification;
use App\JeMengage\Push\Notification\EventCreatedNotification;
use App\JeMengage\Push\Notification\EventLiveBeginNotification;
use App\JeMengage\Push\Notification\EventReminderNotification;
use App\JeMengage\Push\Notification\NationalEventTicketNotification;
use App\JeMengage\Push\Notification\NewsCreatedNotification;
use App\JeMengage\Push\Notification\PrivateMessageNotification;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that each notification resolves the correct scope.
 */
final class NotificationTargetingStrategyTest extends TestCase
{
    public function testEventLiveBeginScopesNational(): void
    {
        self::assertSame('national', EventLiveBeginNotification::create($this->mockEvent())->getScope());
    }

    public function testEventReminderScopesEvent(): void
    {
        self::assertSame('event:1', EventReminderNotification::create($this->mockEvent())->getScope());
    }

    public function testActionCreatedScopesZone(): void
    {
        $zone = $this->createStub(Zone::class);
        $zone->method('getCode')->willReturn('92');

        $action = $this->mockAction(cityZone: $zone);

        self::assertSame('zone:92', ActionCreatedNotification::create($action)->getScope());
    }

    public function testActionCreatedWithoutZoneThrows(): void
    {
        $this->expectException(\RuntimeException::class);

        ActionCreatedNotification::create($this->mockAction());
    }

    public function testActionUpdatedScopesAction(): void
    {
        self::assertSame('action:1', ActionUpdatedNotification::create($this->mockAction())->getScope());
    }

    public function testActionCancelledScopesAction(): void
    {
        self::assertSame('action:1', ActionCancelledNotification::create($this->mockAction())->getScope());
    }

    public function testActionBeginScopesAction(): void
    {
        self::assertSame('action:1', ActionBeginNotification::create($this->mockAction(), true)->getScope());
    }

    public function testNationalEventTicketScopesMeeting(): void
    {
        $event = $this->createStub(NationalEvent::class);
        $event->method('getId')->willReturn(1);

        self::assertSame('meeting:1', NationalEventTicketNotification::create($event)->getScope());
    }

    public function testPrivateMessageScopesPrivateMessage(): void
    {
        $pm = $this->createStub(TimelineItemPrivateMessage::class);
        $pm->notificationTitle = 'Title';
        $pm->notificationDescription = 'Body';
        $pm->method('getId')->willReturn(1);

        self::assertSame('private_message:1', PrivateMessageNotification::create($pm)->getScope());
    }

    public function testAdherentMessageScopesPublication(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('getFromName')->willReturn('Sender');
        $message->method('getSubject')->willReturn('Subject');
        $message->method('getId')->willReturn(1);

        self::assertSame('publication:1', AdherentMessageSentNotification::create($message)->getScope());
    }

    // --- Variable scopes: EventCreated ---

    public function testEventCreatedWithCommitteeScopesCommittee(): void
    {
        $event = $this->mockEvent(hasCommittee: true);

        self::assertSame('committee:1', EventCreatedNotification::create($event)->getScope());
    }

    public function testEventCreatedNationalScopesNational(): void
    {
        $event = $this->mockEvent(isNational: true);

        self::assertSame('national', EventCreatedNotification::create($event)->getScope());
    }

    public function testEventCreatedLocalScopesZone(): void
    {
        $event = $this->mockEvent(hasZone: true);

        self::assertSame('zone:92', EventCreatedNotification::create($event)->getScope());
    }

    // --- Variable scopes: NewsCreated ---

    public function testNewsCreatedWithCommitteeScopesCommittee(): void
    {
        $news = $this->mockNews(hasCommittee: true);

        self::assertSame('committee:1', NewsCreatedNotification::create($news)->getScope());
    }

    public function testNewsCreatedNationalScopesNational(): void
    {
        $news = $this->mockNews(isNational: true);

        self::assertSame('national', NewsCreatedNotification::create($news)->getScope());
    }

    public function testNewsCreatedLocalScopesZone(): void
    {
        $zone = $this->createStub(Zone::class);
        $zone->method('getCode')->willReturn('92');
        $zone->method('getAssemblyZone')->willReturn($zone);

        $news = $this->mockNews();
        $news->method('getZone')->willReturn($zone);

        self::assertSame('zone:92', NewsCreatedNotification::create($news)->getScope());
    }

    // --- Helpers ---

    private function mockEvent(bool $isNational = false, bool $hasCommittee = false, bool $hasZone = false): Event
    {
        $event = $this->createStub(Event::class);
        $event->method('getName')->willReturn('Test Event');
        $event->method('getBeginAt')->willReturn(new \DateTime('+1 day'));
        $event->method('getInlineFormattedAddress')->willReturn('Paris');
        $event->method('isNational')->willReturn($isNational);
        $event->method('getId')->willReturn(1);
        $committee = null;
        if ($hasCommittee) {
            $committee = $this->createStub(Committee::class);
            $committee->method('getId')->willReturn(1);
            $committee->method('getName')->willReturn('Test Committee');
        }
        $event->method('getCommittee')->willReturn($committee);

        if ($hasZone) {
            $zone = $this->createStub(Zone::class);
            $zone->method('getName')->willReturn('Hauts-de-Seine');
            $zone->method('getCode')->willReturn('92');
            $event->method('getAssemblyZone')->willReturn($zone);
        } else {
            $event->method('getAssemblyZone')->willReturn(null);
        }

        return $event;
    }

    private function mockAction(?Zone $cityZone = null): Action
    {
        $action = $this->createStub(Action::class);
        $action->type = 'pap';
        $action->date = new \DateTime('+1 day');
        $action->method('getCityName')->willReturn('Clichy');
        $action->method('getAuthor')->willReturn(null);
        $action->method('getPostalCode')->willReturn('92110');
        $action->method('getAddress')->willReturn('92 bd Victor Hugo');
        $action->method('getZonesOfType')->willReturn($cityZone ? [$cityZone] : []);
        $action->method('getId')->willReturn(1);

        return $action;
    }

    private function mockNews(bool $isNational = false, bool $hasCommittee = false): News
    {
        $news = $this->createStub(News::class);
        $news->method('getTitle')->willReturn('Test News');
        $news->method('getCleanedCroppedText')->willReturn('Test content');
        $news->method('isNational')->willReturn($isNational);
        $committee = null;
        if ($hasCommittee) {
            $committee = $this->createStub(Committee::class);
            $committee->method('getId')->willReturn(1);
        }
        $news->method('getCommittee')->willReturn($committee);

        return $news;
    }
}
