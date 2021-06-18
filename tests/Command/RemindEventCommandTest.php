<?php

namespace Tests\App\Command;

use App\Entity\Notification;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class RemindEventCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    private $notificationRepository;

    public function testCommandForMeetingMode(): void
    {
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

        $this->init();

        $this->notificationRepository = $this->getRepository(Notification::class);
    }

    protected function tearDown(): void
    {
        $this->notificationRepository = null;

        $this->kill();

        parent::tearDown();
    }
}
