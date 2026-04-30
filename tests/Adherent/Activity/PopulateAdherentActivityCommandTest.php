<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Activity;

use App\Adherent\Activity\PopulateAdherentActivityCommand;
use App\Adherent\Activity\SourceTypeEnum;
use App\Messenger\Message\CronjobMessageInterface;
use App\Messenger\Message\LockableMessageInterface;
use PHPUnit\Framework\TestCase;

class PopulateAdherentActivityCommandTest extends TestCase
{
    public function testImplementsCronjobAndLockableInterfaces(): void
    {
        $command = new PopulateAdherentActivityCommand();

        self::assertInstanceOf(CronjobMessageInterface::class, $command);
        self::assertInstanceOf(LockableMessageInterface::class, $command);
    }

    public function testGetLockKeyReturnsConstantValueRegardlessOfSourceType(): void
    {
        $actionHistoryCommand = new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory);
        $hitCommand = new PopulateAdherentActivityCommand(SourceTypeEnum::Hit);

        self::assertSame('populate_adherent_activity', $actionHistoryCommand->getLockKey());
        self::assertSame('populate_adherent_activity', $hitCommand->getLockKey());
    }

    public function testGetLockTtlReturns600Seconds(): void
    {
        $command = new PopulateAdherentActivityCommand();

        self::assertSame(600, $command->getLockTtl());
    }

    public function testIsLockBlockingReturnsTrue(): void
    {
        $command = new PopulateAdherentActivityCommand();

        self::assertTrue($command->isLockBlocking());
    }
}
