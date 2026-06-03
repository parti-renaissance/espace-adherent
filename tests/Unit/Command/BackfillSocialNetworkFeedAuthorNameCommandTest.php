<?php

declare(strict_types=1);

namespace Tests\App\Unit\Command;

use App\Command\BackfillSocialNetworkFeedAuthorNameCommand;
use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Repository\SocialNetworkFeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class BackfillSocialNetworkFeedAuthorNameCommandTest extends TestCase
{
    public function testFillsAuthorNameFromStoredRawPayload(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->rawJson = ['id' => 1, 'name' => 'Jean Dupont'];

        $repository = $this->createMock(SocialNetworkFeedRepository::class);
        $repository->expects(self::once())->method('findWithMissingAuthorName')->with(null)->willReturn([$feed]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $tester = new CommandTester(new BackfillSocialNetworkFeedAuthorNameCommand($repository, $entityManager));
        $tester->setInputs(['yes']);

        self::assertSame(0, $tester->execute([]));
        self::assertSame('Jean Dupont', $feed->authorName);
    }

    public function testSkipsFeedWithoutNameInPayloadAndDoesNotFlush(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->rawJson = ['id' => 1]; // no name captured

        $repository = $this->createMock(SocialNetworkFeedRepository::class);
        $repository->expects(self::once())->method('findWithMissingAuthorName')->with(null)->willReturn([$feed]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $tester = new CommandTester(new BackfillSocialNetworkFeedAuthorNameCommand($repository, $entityManager));

        self::assertSame(0, $tester->execute([]));
        self::assertNull($feed->authorName);
    }

    public function testDryRunReportsWithoutWriting(): void
    {
        $feed = new SocialNetworkFeed();
        $feed->rawJson = ['name' => 'Jean Dupont'];

        $repository = $this->createMock(SocialNetworkFeedRepository::class);
        $repository->expects(self::once())->method('findWithMissingAuthorName')->with(null)->willReturn([$feed]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $tester = new CommandTester(new BackfillSocialNetworkFeedAuthorNameCommand($repository, $entityManager));

        self::assertSame(0, $tester->execute(['--dry-run' => true]));
        self::assertNull($feed->authorName);
    }
}
