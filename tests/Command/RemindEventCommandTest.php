<?php

declare(strict_types=1);

namespace Tests\App\Command;

use App\Entity\Notification;
use Tests\App\AbstractCommandTestCase;

class RemindEventCommandTest extends AbstractCommandTestCase
{
    private $notificationRepository;

    public function testCommandForMeetingMode(): void
    {
        $this->markTestSkipped('Commands are tested in subprocess, hence we can not use Chronos to mock the dates for the tests. Skipping this test until we remove our dependency to cackephp/chronos package.');

        $notifications = $this->notificationRepository->findAll();
        self::assertEmpty($notifications);

        $output = $this->runCommand('app:events:remind', ['mode' => 'meeting']);
        $output = $output->getDisplay();
        self::assertStringContainsString('1 events has been reminded.', $output);

        $notifications = $this->notificationRepository->findAll();
        self::assertCount(1, $notifications);

        /** @var Notification $notification */
        $notification = current($notifications);
        self::assertSame('EventReminderNotification', $notification->getNotificationClass());
        self::assertSame('Votre événement commence bientôt', $notification->getTitle());
        self::assertStringStartsWith('Réunion de réflexion bellifontaine • ', $notification->getBody());
        self::assertStringEndsWith(' • 40 Rue Grande, 77300 Fontainebleau', $notification->getBody());
        self::assertEmpty($notification->getTopic());
        self::assertSame([
            'token-francis-jemarche-1',
            'token-francis-jemarche-2',
        ], $notification->getTokens());
    }

    public function testCommandForOnlineMode(): void
    {
        $this->markTestSkipped('Commands are tested in subprocess, hence we can not use Chronos to mock the dates for the tests. Skipping this test until we remove our dependency to cackephp/chronos package.');

        $notifications = $this->notificationRepository->findAll();
        self::assertEmpty($notifications);

        $output = $this->runCommand('app:events:remind', ['mode' => 'online']);
        $output = $output->getDisplay();
        self::assertStringContainsString('1 events has been reminded.', $output);

        $notifications = $this->notificationRepository->findAll();

        self::assertCount(1, $notifications);

        /** @var Notification $notification */
        $notification = current($notifications);
        self::assertSame('EventReminderNotification', $notification->getNotificationClass());
        self::assertSame('Votre événement commence bientôt', $notification->getTitle());
        self::assertStringStartsWith('Nouvel événement online • ', $notification->getBody());
        self::assertEmpty($notification->getTopic());
        self::assertSame([
            'token-francis-jemarche-1',
            'token-francis-jemarche-2',
        ], $notification->getTokens());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationRepository = $this->getRepository(Notification::class);
    }

    protected function tearDown(): void
    {
        $this->notificationRepository = null;

        parent::tearDown();
    }
}
