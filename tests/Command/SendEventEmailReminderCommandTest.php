<?php

namespace Tests\App\Command;

use App\Entity\Event\Event;
use App\Repository\Event\EventRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('functional')]
class SendEventEmailReminderCommandTest extends AbstractCommandTestCase
{
    private ?EventRepository $eventRepository = null;

    public function testCommandSuccess(): void
    {
        self::assertSame(11, $this->countEventsToRemind());

        $output = $this->runCommand('app:event:send-email-reminder');
        $output = $output->getDisplay();

        self::assertStringContainsString('[OK] 11 events has been reminded.', $output);
        self::assertSame(0, $this->countEventsToRemind());

        // Ensure second call of same command has no event left to remind
        $output = $this->runCommand('app:event:send-email-reminder');
        $output = $output->getDisplay();

        self::assertStringContainsString('[OK] 0 events has been reminded.', $output);
    }

    private function countEventsToRemind(): int
    {
        $startAfter = (new \DateTime())->setTime(7, 0);
        $startBefore = (clone $startAfter)->modify('+1 day');

        $events = $this->eventRepository->findEventsToRemindByEmail($startAfter, $startBefore);

        return \count($events);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = $this->getRepository(Event::class);
    }

    protected function tearDown(): void
    {
        $this->eventRepository = null;

        parent::tearDown();
    }
}
