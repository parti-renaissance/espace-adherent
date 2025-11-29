<?php

declare(strict_types=1);

namespace Tests\App\Command;

use App\Entity\Reporting\DeclaredMandateHistory;
use App\Mailer\Message\Renaissance\RenaissanceDeclaredMandateNotificationMessage;
use App\Repository\Reporting\DeclaredMandateHistoryRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('functional')]
class SendDeclaredMandateChangeNotificationCommandTest extends AbstractCommandTestCase
{
    private ?DeclaredMandateHistoryRepository $declaredMandateHistoryRepository = null;

    public function testCommandSuccess(): void
    {
        self::assertCount(3, $this->declaredMandateHistoryRepository->findToNotify());

        $output = $this->runCommand('app:declared-mandates:notify-changes');
        $output = $output->getDisplay();
        self::assertStringContainsString('Will notify 2 administrator(s) about 3 new declared mandate historie(s)', $output);
        self::assertStringContainsString('Will notify 4 manager(s) of department 92 about 1 new declared mandate historie(s)', $output);
        self::assertStringContainsString('Will mark 3 new declared mandate histories as notified', $output);
        self::assertStringContainsString('Notifications sent!', $output);

        self::assertEmpty($this->declaredMandateHistoryRepository->findToNotify());

        $this->assertCountMails(3, RenaissanceDeclaredMandateNotificationMessage::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->declaredMandateHistoryRepository = $this->getRepository(DeclaredMandateHistory::class);
    }

    protected function tearDown(): void
    {
        $this->declaredMandateHistoryRepository = null;

        parent::tearDown();
    }
}
