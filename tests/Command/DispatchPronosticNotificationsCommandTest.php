<?php

declare(strict_types=1);

namespace Tests\App\Command;

use App\Command\DispatchPronosticNotificationsCommand;
use App\Entity\Pronostic\Pronostic;
use App\JeMengage\Push\Command\PronosticNotificationCommand;
use App\Pronostic\PronosticReminderTypeEnum;
use App\Repository\Pronostic\PronosticRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DispatchPronosticNotificationsCommandTest extends TestCase
{
    private PronosticRepository $pronosticRepository;
    private MessageBusInterface $bus;
    private EntityManagerInterface $entityManager;
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->pronosticRepository = $this->createStub(PronosticRepository::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->entityManager = $this->createStub(EntityManagerInterface::class);

        $command = new DispatchPronosticNotificationsCommand(
            $this->bus,
            $this->pronosticRepository,
            $this->entityManager,
        );

        $this->tester = new CommandTester($command);
    }

    public function testDoesNothingWhenNoDisplayedPronostic(): void
    {
        $this->pronosticRepository->method('findDisplayed')->willReturn(null);
        $this->bus->expects(self::never())->method('dispatch');

        $this->tester->execute([]);

        self::assertSame(0, $this->tester->getStatusCode());
    }

    public function testDispatchesCreationPushWhenNoReminder(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+2 days');
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback($this->isPushOfType(PronosticReminderTypeEnum::CREATION)))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::CREATION));
    }

    public function testOnlyDispatchesCreationWhenJMinus1ThresholdAlreadyPassed(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+12 hours', beginAt: '-2 days');
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback($this->isPushOfType(PronosticReminderTypeEnum::CREATION)))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::CREATION));
        self::assertTrue($pronostic->jMinus1Notified);
    }

    public function testSkipsCreationBeforePronosticBeginAt(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+2 days', beginAt: '+1 hour');
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);
        $this->bus->expects(self::never())->method('dispatch');

        $this->tester->execute([]);

        self::assertFalse($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::CREATION));
    }

    public function testSkipsRemindersBeforePronosticBeginAt(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+12 hours', beginAt: '+1 hour');
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);
        $this->bus->expects(self::never())->method('dispatch');

        $this->tester->execute([]);

        self::assertFalse($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::J_MINUS_1));
    }

    public function testDispatchesCreationAndSkipsJMinus1WhenPronosticBeginsAfterJMinus1Threshold(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+12 hours', beginAt: '-11 hours');

        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback($this->isPushOfType(PronosticReminderTypeEnum::CREATION)))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::CREATION));
        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::J_MINUS_1));
    }

    public function testSkipsCreationWhenReminderAlreadyExists(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+2 days');
        $pronostic->creationNotified = true;
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);
        $this->bus->expects(self::never())->method('dispatch');

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::CREATION));
    }

    public function testDispatchesJMinus1PushWithinOneDay(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+12 hours');
        $pronostic->creationNotified = true;
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback($this->isPushOfType(PronosticReminderTypeEnum::J_MINUS_1)))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::J_MINUS_1));
    }

    public function testDispatchesHMinus1PushWithinOneHour(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+30 minutes');
        $pronostic->creationNotified = true;
        $pronostic->jMinus1Notified = true;
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback($this->isPushOfType(PronosticReminderTypeEnum::H_MINUS_1)))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::H_MINUS_1));
    }

    public function testDispatchesHMinus1WhenPronosticBeginsAfterHMinus1Threshold(): void
    {
        $pronostic = $this->makePronostic(beginAt: '-30 minutes', matchAt: '+20 minutes');
        $pronostic->creationNotified = true;
        $pronostic->jMinus1Notified = true;
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback($this->isPushOfType(PronosticReminderTypeEnum::H_MINUS_1)))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::H_MINUS_1));
    }

    public function testDispatchesHMinus5MinPushWithinFiveMinutes(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+3 minutes');
        $pronostic->creationNotified = true;
        $pronostic->jMinus1Notified = true;
        $pronostic->hMinus1Notified = true;
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback($this->isPushOfType(PronosticReminderTypeEnum::H_MINUS_5_MIN)))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::H_MINUS_5_MIN));
    }

    public function testDispatchesResultsPushWhenResultPublished(): void
    {
        $pronostic = $this->makePronostic(matchAt: '-1 hour', resultPublished: true);
        $pronostic->creationNotified = true;
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback($this->isPushOfType(PronosticReminderTypeEnum::RESULTS)))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::RESULTS));
    }

    public function testSkipsResultsWhenReminderAlreadyExists(): void
    {
        $pronostic = $this->makePronostic(matchAt: '-1 hour', resultPublished: true);
        $pronostic->creationNotified = true;
        $pronostic->resultNotified = true;
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);
        $this->bus->expects(self::never())->method('dispatch');

        $this->tester->execute([]);

        self::assertTrue($pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::RESULTS));
    }

    private function isPushOfType(PronosticReminderTypeEnum $type): callable
    {
        return static fn ($command) => $command instanceof PronosticNotificationCommand && $type === $command->type;
    }

    private function makePronostic(string $matchAt, bool $resultPublished = false, string $beginAt = '-1 day'): Pronostic
    {
        $pronostic = new Pronostic();
        $pronostic->title = 'Test';
        $pronostic->team1 = 'France';
        $pronostic->team2 = 'Sénégal';
        $pronostic->beginAt = new \DateTimeImmutable($beginAt);
        $pronostic->matchAt = new \DateTimeImmutable($matchAt);
        $pronostic->gabrielTeam1Score = 1;
        $pronostic->gabrielTeam2Score = 0;

        if ($resultPublished) {
            $pronostic->resultTeam1Score = 2;
            $pronostic->resultTeam2Score = 1;
            $pronostic->resultPublishedAt = new \DateTimeImmutable('-30 minutes');
        }

        return $pronostic;
    }
}
