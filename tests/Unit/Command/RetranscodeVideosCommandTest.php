<?php

declare(strict_types=1);

namespace Tests\App\Unit\Command;

use App\Command\RetranscodeVideosCommand;
use App\Video\Transcoding\TranscoderCapacityDeferral;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\MessageBusInterface;

final class RetranscodeVideosCommandTest extends TestCase
{
    public function testInvalidStatusIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid --status');

        $this->tester()->execute(['--status' => 'unknown']);
    }

    public function testPendingWithoutOlderThanIsRejected(): void
    {
        // The age guard throws before any DB access, so stub collaborators are never touched.
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('--status=pending requires --older-than');

        $this->tester()->execute(['--status' => 'pending']);
    }

    public function testPendingBelowDeferralHorizonIsRejected(): void
    {
        self::assertGreaterThan(5, TranscoderCapacityDeferral::DEFERRAL_HORIZON_MINUTES);

        $this->expectException(\InvalidArgumentException::class);

        $this->tester()->execute(['--status' => 'pending', '--older-than' => '5']);
    }

    private function tester(): CommandTester
    {
        return new CommandTester(new RetranscodeVideosCommand(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(MessageBusInterface::class),
        ));
    }
}
