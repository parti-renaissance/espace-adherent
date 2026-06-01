<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Tag\Listener;

use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\Adherent\Tag\Listener\RefreshTagsListener;
use App\Entity\Adherent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class RefreshTagsListenerTest extends TestCase
{
    public function testUserUpdatedRefreshesSignupAccountTags(): void
    {
        $uuid = Uuid::v4();

        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        $adherent->method('getUuid')->willReturn($uuid);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (object $command) use ($uuid): bool {
                return $command instanceof AsyncRefreshAdherentTagCommand && $command->getUuid()->equals($uuid);
            }))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        new RefreshTagsListener($bus)->refreshSignupAccountTags(new UserEvent($adherent));
    }

    public function testUserUpdatedDoesNotRefreshNonSignupAccount(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = false;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        new RefreshTagsListener($bus)->refreshSignupAccountTags(new UserEvent($adherent));
    }

    public function testUserUpdatedIsWiredToTheSignupGuardedHandler(): void
    {
        self::assertSame(
            'refreshSignupAccountTags',
            RefreshTagsListener::getSubscribedEvents()[UserEvents::USER_UPDATED] ?? null
        );
    }
}
