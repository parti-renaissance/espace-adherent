<?php

declare(strict_types=1);

namespace Tests\App\Unit\Command;

use App\Command\MailchimpCleanupStaticSegmentsCommand;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Driver;
use App\Repository\AdherentMessageTargetedRepository;
use App\Repository\MailchimpCampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MailchimpCleanupStaticSegmentsCommandTest extends TestCase
{
    private const string LIST_ID = 'list-main-abc';

    public function testExecuteNoExpiredCampaignsNoOrphansAllCountsZero(): void
    {
        $campaignRepo = $this->createMock(MailchimpCampaignRepository::class);
        $campaignRepo->expects(self::once())
            ->method('findExpiredForCleanup')
            ->with(MailchimpCleanupStaticSegmentsCommand::EXPIRED_RETENTION_DAYS)
            ->willReturn([]);

        $targetedRepo = $this->createMock(AdherentMessageTargetedRepository::class);
        $targetedRepo->expects(self::once())
            ->method('deleteForMessagesSentBefore')
            ->willReturn(0);

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('deleteStaticSegment');
        $driver->expects(self::once())
            ->method('getAllSegmentsWithPrefix')
            ->with(MailchimpCleanupStaticSegmentsCommand::SEGMENT_NAME_PREFIX, self::LIST_ID)
            ->willReturn(new \EmptyIterator());

        $command = $this->buildCommand($campaignRepo, $targetedRepo, $driver);

        $tester = new CommandTester($command);
        $exit = $tester->execute([]);

        self::assertSame(0, $exit);
        self::assertStringContainsString('0 segment(s) de campagnes expirées supprimé(s)', $tester->getDisplay());
        self::assertStringContainsString('0 segment(s) orphelin(s) supprimé(s)', $tester->getDisplay());
        self::assertStringContainsString('0 ligne(s) adherent_message_targeted purgée(s)', $tester->getDisplay());
    }

    public function testExecuteExpiredCampaignsDeletesEachAndResetsRefs(): void
    {
        $message = new AdherentMessage();
        $campaign1 = new MailchimpCampaign($message);
        $campaign1->setStaticSegmentId(101);
        $campaign1->setMailchimpSegmentName('campaign_aaa');

        $campaign2 = new MailchimpCampaign($message);
        $campaign2->setStaticSegmentId(102);
        $campaign2->setMailchimpSegmentName('campaign_bbb');

        $campaignRepo = $this->createStub(MailchimpCampaignRepository::class);
        $campaignRepo->method('findExpiredForCleanup')->willReturn([$campaign1, $campaign2]);

        $targetedRepo = $this->createStub(AdherentMessageTargetedRepository::class);
        $targetedRepo->method('deleteForMessagesSentBefore')->willReturn(0);

        $deleted = [];
        $driver = $this->createMock(Driver::class);
        $driver->expects(self::exactly(2))
            ->method('deleteStaticSegment')
            ->willReturnCallback(function (int $id) use (&$deleted): bool {
                $deleted[] = $id;

                return true;
            });
        $driver->method('getAllSegmentsWithPrefix')->willReturn(new \EmptyIterator());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::exactly(2))->method('flush');

        $command = $this->buildCommand($campaignRepo, $targetedRepo, $driver, $em);
        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame([101, 102], $deleted);
        self::assertNull($campaign1->getStaticSegmentId());
        self::assertNull($campaign1->getMailchimpSegmentName());
        self::assertNull($campaign2->getStaticSegmentId());
        self::assertStringContainsString('2 segment(s) de campagnes expirées supprimé(s)', $tester->getDisplay());
    }

    public function testExecuteDriverThrowsOnExpiredDeleteLoggedAndContinues(): void
    {
        $message = new AdherentMessage();
        $campaign1 = new MailchimpCampaign($message);
        $campaign1->setStaticSegmentId(201);
        $campaign2 = new MailchimpCampaign($message);
        $campaign2->setStaticSegmentId(202);

        $campaignRepo = $this->createStub(MailchimpCampaignRepository::class);
        $campaignRepo->method('findExpiredForCleanup')->willReturn([$campaign1, $campaign2]);

        $targetedRepo = $this->createStub(AdherentMessageTargetedRepository::class);
        $targetedRepo->method('deleteForMessagesSentBefore')->willReturn(0);

        $driver = $this->createStub(Driver::class);
        $driver->method('deleteStaticSegment')
            ->willReturnCallback(static function (int $id): bool {
                if (201 === $id) {
                    throw new \RuntimeException('mailchimp 500');
                }

                return true;
            });
        $driver->method('getAllSegmentsWithPrefix')->willReturn(new \EmptyIterator());

        $em = $this->createMock(EntityManagerInterface::class);
        // flush only called for the successful one
        $em->expects(self::once())->method('flush');

        $command = $this->buildCommand($campaignRepo, $targetedRepo, $driver, $em);
        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame(201, $campaign1->getStaticSegmentId(), 'campaign1 staticSegmentId stays at 201 since delete failed');
        self::assertNull($campaign2->getStaticSegmentId(), 'campaign2 cleared after success');
        self::assertStringContainsString('1 segment(s) de campagnes expirées supprimé(s)', $tester->getDisplay());
    }

    public function testExecuteOrphanSegmentsOlderThanThresholdDeleted(): void
    {
        $oldCreatedAt = new \DateTimeImmutable()->modify('-48 hours')->format(\DATE_ATOM);
        $recentCreatedAt = new \DateTimeImmutable()->modify('-1 hour')->format(\DATE_ATOM);

        $segments = [
            ['id' => 301, 'name' => 'campaign_old_orphan', 'created_at' => $oldCreatedAt],
            ['id' => 302, 'name' => 'campaign_recent', 'created_at' => $recentCreatedAt],
            ['id' => 303, 'name' => 'campaign_linked_active', 'created_at' => $oldCreatedAt],
        ];

        $campaignRepo = $this->createMock(MailchimpCampaignRepository::class);
        $campaignRepo->method('findExpiredForCleanup')->willReturn([]);
        $campaignRepo->expects(self::exactly(2)) // checked for old segments only
            ->method('isLinkedToActiveCampaign')
            ->willReturnMap([
                [301, false], // orphan → delete
                [303, true],  // linked → skip
            ]);

        $targetedRepo = $this->createStub(AdherentMessageTargetedRepository::class);
        $targetedRepo->method('deleteForMessagesSentBefore')->willReturn(0);

        $deleted = [];
        $driver = $this->createMock(Driver::class);
        $driver->expects(self::once())
            ->method('deleteStaticSegment')
            ->with(301)
            ->willReturnCallback(function (int $id) use (&$deleted): bool {
                $deleted[] = $id;

                return true;
            });
        $driver->method('getAllSegmentsWithPrefix')->willReturn(new \ArrayIterator($segments));

        $command = $this->buildCommand($campaignRepo, $targetedRepo, $driver);
        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame([301], $deleted);
        self::assertStringContainsString('1 segment(s) orphelin(s) supprimé(s)', $tester->getDisplay());
    }

    public function testExecutePurgeTargetedReturnsCountDisplayedInOutput(): void
    {
        $campaignRepo = $this->createStub(MailchimpCampaignRepository::class);
        $campaignRepo->method('findExpiredForCleanup')->willReturn([]);

        $captured = null;
        $targetedRepo = $this->createMock(AdherentMessageTargetedRepository::class);
        $targetedRepo->expects(self::once())
            ->method('deleteForMessagesSentBefore')
            ->willReturnCallback(function (\DateTimeInterface $threshold) use (&$captured): int {
                $captured = $threshold;

                return 1234;
            });

        $driver = $this->createStub(Driver::class);
        $driver->method('getAllSegmentsWithPrefix')->willReturn(new \EmptyIterator());

        $command = $this->buildCommand($campaignRepo, $targetedRepo, $driver);
        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertStringContainsString('1234 ligne(s) adherent_message_targeted purgée(s)', $tester->getDisplay());

        $expected = new \DateTimeImmutable()->modify(\sprintf('-%d days', MailchimpCleanupStaticSegmentsCommand::TARGETED_RETENTION_DAYS));
        self::assertEqualsWithDelta($expected->getTimestamp(), $captured->getTimestamp(), 5, 'threshold ~ now - 365d');
    }

    private function buildCommand(
        MailchimpCampaignRepository $campaignRepo,
        AdherentMessageTargetedRepository $targetedRepo,
        Driver $driver,
        ?EntityManagerInterface $em = null,
    ): MailchimpCleanupStaticSegmentsCommand {
        $em ??= $this->createStub(EntityManagerInterface::class);

        $mapping = $this->createStub(MailchimpObjectIdMapping::class);
        $mapping->method('getMainListId')->willReturn(self::LIST_ID);

        return new MailchimpCleanupStaticSegmentsCommand(
            $em,
            $campaignRepo,
            $targetedRepo,
            $mapping,
            $driver,
        );
    }
}
