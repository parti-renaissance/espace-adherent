<?php

declare(strict_types=1);

namespace Tests\App\Repository\Poll;

use App\Entity\Poll\Poll;
use App\Repository\Poll\PollRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class PollRepositoryTest extends AbstractKernelTestCase
{
    private ?PollRepository $pollRepository = null;

    public function testReturnsOverlappingPublishedPoll(): void
    {
        $existing = $this->persistPoll('2099-01-10 00:00:00', '2099-01-20 00:00:00', true);

        $candidate = $this->buildPoll('2099-01-15 00:00:00', '2099-01-25 00:00:00');

        self::assertSame($existing->getId(), $this->pollRepository->findConflictingPublishedPoll($candidate)?->getId());
    }

    public function testIgnoresUnpublishedPoll(): void
    {
        $this->persistPoll('2099-02-10 00:00:00', '2099-02-20 00:00:00', false);

        $candidate = $this->buildPoll('2099-02-15 00:00:00', '2099-02-25 00:00:00');

        self::assertNull($this->pollRepository->findConflictingPublishedPoll($candidate));
    }

    public function testAllowsBackToBackPolls(): void
    {
        $this->persistPoll('2099-03-10 00:00:00', '2099-03-20 00:00:00', true);

        $candidate = $this->buildPoll('2099-03-20 00:00:00', '2099-03-30 00:00:00');

        self::assertNull($this->pollRepository->findConflictingPublishedPoll($candidate));
    }

    public function testExcludesTheEditedPollItself(): void
    {
        $poll = $this->persistPoll('2099-04-10 00:00:00', '2099-04-20 00:00:00', true);

        self::assertNull($this->pollRepository->findConflictingPublishedPoll($poll));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->pollRepository = $this->getRepository(Poll::class);
    }

    protected function tearDown(): void
    {
        $this->pollRepository = null;

        parent::tearDown();
    }

    private function buildPoll(string $startAt, string $finishAt, bool $published = true): Poll
    {
        return new Poll(
            Uuid::v4(),
            'Sondage de test',
            new \DateTimeImmutable($finishAt),
            $published,
            new \DateTimeImmutable($startAt),
        );
    }

    private function persistPoll(string $startAt, string $finishAt, bool $published): Poll
    {
        $poll = $this->buildPoll($startAt, $finishAt, $published);

        $this->manager->persist($poll);
        $this->manager->flush();

        return $poll;
    }
}
