<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Alert\Provider;

use App\Entity\Adherent;
use App\Entity\Poll\Poll;
use App\JeMengage\Alert\AlertTypeEnum;
use App\JeMengage\Alert\Provider\PollAlertProvider;
use App\Repository\Poll\PollRepository;
use App\Repository\Poll\VoteRepository;
use PHPUnit\Framework\TestCase;

final class PollAlertProviderTest extends TestCase
{
    public function testNoAlertWhenNoActivePoll(): void
    {
        $pollRepository = $this->createStub(PollRepository::class);
        $pollRepository->method('findActivePollForAlert')->willReturn(null);

        $voteRepository = $this->createMock(VoteRepository::class);
        $voteRepository->expects($this->never())->method('hasVoted');

        self::assertSame([], new PollAlertProvider($pollRepository, $voteRepository)->getAlerts(null));
    }

    public function testAnonymousUserGetsAlertWithoutParticipationState(): void
    {
        $poll = $this->poll();

        $voteRepository = $this->createMock(VoteRepository::class);
        $voteRepository->expects($this->never())->method('hasVoted');

        $alerts = new PollAlertProvider($this->pollRepository($poll), $voteRepository)->getAlerts(null);

        self::assertCount(1, $alerts);
        $alert = $alerts[0];
        self::assertSame(AlertTypeEnum::POLL, $alert->type);
        self::assertSame('Sondage', $alert->label);
        self::assertSame('Plutôt thé ou café ?', $alert->title);
        self::assertSame('Je donne mon avis', $alert->ctaLabel);
        self::assertSame('/sondage/'.$poll->getUuid()->toRfc4122(), $alert->ctaUrl);
        self::assertSame($poll->getUuid()->toRfc4122(), $alert->data['uuid']);
        self::assertSame('Plutôt thé ou café ?', $alert->data['question']);
        self::assertSame($poll->getStartAt()->format(\DateTimeInterface::ATOM), $alert->data['start_at']);
        self::assertSame($poll->getFinishAt()->format(\DateTimeInterface::ATOM), $alert->data['finish_at']);
        self::assertNull($alert->data['participated']);
        self::assertEquals($poll->getFinishAt(), $alert->date);
    }

    public function testAdherentWithoutVoteGetsNotParticipatedAlert(): void
    {
        $poll = $this->poll();
        $adherent = $this->createStub(Adherent::class);

        $voteRepository = $this->createStub(VoteRepository::class);
        $voteRepository->method('hasVoted')->willReturn(false);

        $alerts = new PollAlertProvider($this->pollRepository($poll), $voteRepository)->getAlerts($adherent);

        self::assertFalse($alerts[0]->data['participated']);
        self::assertSame('Je donne mon avis', $alerts[0]->ctaLabel);
    }

    public function testAdherentWithVoteGetsParticipatedAlert(): void
    {
        $poll = $this->poll();
        $adherent = $this->createStub(Adherent::class);

        $voteRepository = $this->createStub(VoteRepository::class);
        $voteRepository->method('hasVoted')->willReturn(true);

        $alerts = new PollAlertProvider($this->pollRepository($poll), $voteRepository)->getAlerts($adherent);

        self::assertTrue($alerts[0]->data['participated']);
        self::assertSame('Voir', $alerts[0]->ctaLabel);
    }

    private function pollRepository(Poll $poll): PollRepository
    {
        $pollRepository = $this->createStub(PollRepository::class);
        $pollRepository->method('findActivePollForAlert')->willReturn($poll);

        return $pollRepository;
    }

    private function poll(): Poll
    {
        return new Poll(
            null,
            'Plutôt thé ou café ?',
            new \DateTimeImmutable('+1 day'),
            true,
            new \DateTimeImmutable('-1 hour'),
        );
    }
}
