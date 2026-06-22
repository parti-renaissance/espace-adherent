<?php

declare(strict_types=1);

namespace Tests\App\Command;

use App\Command\DispatchPronosticNotificationsCommand;
use App\Entity\Pronostic\Pronostic;
use App\JeMengage\Push\Command\PronosticNotificationCommand;
use App\Mailer\MailerService;
use App\Pronostic\PronosticReminderTypeEnum;
use App\Repository\AdherentRepository;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticReminderRepository;
use App\Repository\Pronostic\PronosticRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class DispatchPronosticNotificationsCommandTest extends TestCase
{
    private PronosticRepository $pronosticRepository;
    private PronosticReminderRepository $reminderRepository;
    private MessageBusInterface $bus;
    private MailerService $mailer;
    private AdherentRepository $adherentRepository;
    private PronosticParticipationRepository $participationRepository;
    private EntityManagerInterface $entityManager;
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->pronosticRepository = $this->createStub(PronosticRepository::class);
        $this->reminderRepository = $this->createStub(PronosticReminderRepository::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->mailer = $this->createMock(MailerService::class);
        $this->adherentRepository = $this->createStub(AdherentRepository::class);
        $this->participationRepository = $this->createStub(PronosticParticipationRepository::class);
        $this->entityManager = $this->createStub(EntityManagerInterface::class);

        $query = $this->createStub(Query::class);
        $query->method('getResult')->willReturn([]);
        $qb = $this->createStub(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);
        $this->adherentRepository->method('createSubscribersQueryBuilder')->willReturn($qb);
        $this->participationRepository->method('findAllForPronostic')->willReturn([]);

        $command = new DispatchPronosticNotificationsCommand(
            $this->bus,
            $this->mailer,
            $this->pronosticRepository,
            $this->participationRepository,
            $this->reminderRepository,
            $this->adherentRepository,
            $this->entityManager,
        );

        $this->tester = new CommandTester($command);
    }

    public function testDoesNothingWhenNoDisplayedPronostic(): void
    {
        $this->pronosticRepository->method('findDisplayed')->willReturn(null);
        $this->bus->expects(self::never())->method('dispatch');
        $this->mailer->expects(self::never())->method('sendMessage');

        $this->tester->execute([]);

        self::assertSame(0, $this->tester->getStatusCode());
    }

    public function testSendsCreationEmailWhenNoReminder(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+2 days');
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);
        $this->reminderRepository->method('has')->willReturn(false);
        $this->bus->expects(self::never())->method('dispatch');
        $this->mailer->expects(self::never())->method('sendMessage');

        $this->tester->execute([]);

        self::assertStringContainsString('Mail de création envoyé', $this->tester->getDisplay());
    }

    public function testSkipsCreationWhenReminderAlreadyExists(): void
    {
        $pronostic = $this->makePronostic(matchAt: '+2 days');
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);
        $this->reminderRepository->method('has')->willReturn(true);
        $this->bus->expects(self::never())->method('dispatch');
        $this->mailer->expects(self::never())->method('sendMessage');

        $this->tester->execute([]);

        self::assertStringNotContainsString('Mail de création envoyé', $this->tester->getDisplay());
    }

    public function testDispatchesResultsPushWhenResultPublished(): void
    {
        $pronostic = $this->makePronostic(matchAt: '-1 hour', resultPublished: true);
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);
        $this->reminderRepository->method('has')->willReturnMap([
            [$pronostic, PronosticReminderTypeEnum::CREATION, true],
            [$pronostic, PronosticReminderTypeEnum::RESULTS, false],
        ]);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(fn($cmd) => $cmd instanceof PronosticNotificationCommand && $cmd->type === PronosticReminderTypeEnum::RESULTS))
            ->willReturn(new Envelope(new \stdClass()));
        $this->mailer->expects(self::never())->method('sendMessage');

        $this->tester->execute([]);

        self::assertStringContainsString('Push + mails de résultats envoyés', $this->tester->getDisplay());
    }

    public function testSkipsResultsWhenReminderAlreadyExists(): void
    {
        $pronostic = $this->makePronostic(matchAt: '-1 hour', resultPublished: true);
        $this->pronosticRepository->method('findDisplayed')->willReturn($pronostic);
        $this->reminderRepository->method('has')->willReturn(true);
        $this->bus->expects(self::never())->method('dispatch');
        $this->mailer->expects(self::never())->method('sendMessage');

        $this->tester->execute([]);

        self::assertStringNotContainsString('Push + mails de résultats envoyés', $this->tester->getDisplay());
    }

    private function makePronostic(string $matchAt, bool $resultPublished = false): Pronostic
    {
        $pronostic = new Pronostic();
        $pronostic->title = 'Test';
        $pronostic->team1 = 'France';
        $pronostic->team2 = 'Sénégal';
        $pronostic->beginAt = new \DateTimeImmutable('-1 day');
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
