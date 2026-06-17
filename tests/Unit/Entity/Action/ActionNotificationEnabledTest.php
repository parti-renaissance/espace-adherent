<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\Action;

use App\Entity\Action\Action;
use App\JeMengage\Push\Command\NotifyForActionCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * The cancellation push runs asynchronously, after CancelActionController has flushed the cancelled
 * action. When the handler reloads the action it is already cancelled, so isNotificationEnabled() must
 * still allow the cancellation notification — while continuing to block create/update on a cancelled action.
 */
class ActionNotificationEnabledTest extends TestCase
{
    public function testCreateAndCancelNotificationsAreEnabledOnScheduledAction(): void
    {
        $action = new Action();

        self::assertTrue($action->isNotificationEnabled($this->command(NotifyForActionCommand::EVENT_CREATE)));
        self::assertTrue($action->isNotificationEnabled($this->command(NotifyForActionCommand::EVENT_CANCEL)));
    }

    public function testCancelNotificationIsEnabledOnCancelledActionButCreateIsBlocked(): void
    {
        $action = new Action();
        $action->cancel();

        self::assertTrue(
            $action->isNotificationEnabled($this->command(NotifyForActionCommand::EVENT_CANCEL)),
            'The cancellation push must still be sent for an already-cancelled action'
        );
        self::assertFalse(
            $action->isNotificationEnabled($this->command(NotifyForActionCommand::EVENT_CREATE)),
            'A create push must remain suppressed for a cancelled action'
        );
        self::assertFalse(
            $action->isNotificationEnabled($this->command(NotifyForActionCommand::EVENT_UPDATE)),
            'An update push must remain suppressed for a cancelled action'
        );
    }

    private function command(string $event): NotifyForActionCommand
    {
        return new NotifyForActionCommand(Uuid::v4(), $event);
    }
}
