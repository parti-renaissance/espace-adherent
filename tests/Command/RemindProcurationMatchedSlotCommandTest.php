<?php

namespace Tests\App\Command;

use App\DataFixtures\ORM\LoadProcurationV2ElectionData;
use App\Entity\ProcurationV2\Round;
use App\Mailer\Message\Procuration\V2\ProcurationMatchReminderMessage;
use App\Repository\Procuration\RoundRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('functional')]
class RemindProcurationMatchedSlotCommandTest extends AbstractCommandTestCase
{
    private ?RoundRepository $roundRepository = null;

    public function testCommandSuccess(): void
    {
        $round = $this->roundRepository->findOneByUuid(LoadProcurationV2ElectionData::UUID_LEGISLATIVES_ROUND_1);

        $this->assertInstanceOf(Round::class, $round);
        $this->assertCountMails(0, ProcurationMatchReminderMessage::class);

        $output = $this->runCommand('app:procuration:remind-matched-slot', [], ['interactive' => false]);
        $output = $output->getDisplay();

        self::assertStringContainsString('Législatives 2024   Premier tour', $output);
        self::assertStringContainsString('Found 1 matched request slot(s) to remind for given round.', $output);
        self::assertStringContainsString('[OK] Done.', $output);

        $this->assertCountMails(1, ProcurationMatchReminderMessage::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->roundRepository = $this->getRepository(Round::class);
    }

    protected function tearDown(): void
    {
        $this->roundRepository = null;

        parent::tearDown();
    }
}
