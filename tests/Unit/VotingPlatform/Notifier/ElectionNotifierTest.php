<?php

declare(strict_types=1);

namespace Tests\App\Unit\VotingPlatform\Notifier;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Mailer\MailerService;
use App\Repository\AdherentRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Notifier\ElectionNotifier;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\CommonMarkConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ElectionNotifierTest extends TestCase
{
    private (MailerService&MockObject)|(MailerService&Stub) $mailer;
    private UrlGeneratorInterface&Stub $urlGenerator;
    private (EntityManagerInterface&MockObject)|(EntityManagerInterface&Stub) $entityManager;
    private VoterRepository&MockObject $voterRepository;
    private AdherentRepository&MockObject $adherentRepository;
    private ElectionNotifier $notifier;

    protected function setUp(): void
    {
        $this->mailer = $this->createStub(MailerService::class);
        $this->urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->voterRepository = $this->createMock(VoterRepository::class);
        $this->adherentRepository = $this->createMock(AdherentRepository::class);

        $this->urlGenerator
            ->method('generate')
            ->willReturn('http://localhost/elections/xxx')
        ;

        $this->rebuildNotifier();
    }

    private function rebuildNotifier(): void
    {
        $this->notifier = new ElectionNotifier(
            $this->mailer,
            $this->urlGenerator,
            $this->entityManager,
            $this->voterRepository,
            $this->adherentRepository,
            new CommonMarkConverter(),
        );
    }

    public function testNotifyElectionVoteIsOpenNationalWithoutFlagUsesVotersList(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: false, notifyPotentialElectorate: false);

        $this->voterRepository
            ->expects(self::atLeastOnce())
            ->method('findForElection')
            ->willReturn([])
        ;
        $this->adherentRepository
            ->expects(self::never())
            ->method('findRenaissanceAdherentsForElection')
        ;

        $this->notifier->notifyElectionVoteIsOpen($election);
    }

    public function testNotifyElectionVoteIsOpenNationalWithFlagUsesPotentialElectorateRepoMethod(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: false, notifyPotentialElectorate: true);

        $this->adherentRepository
            ->expects(self::atLeastOnce())
            ->method('findRenaissanceAdherentsForElection')
            ->with($election, false, self::anything(), self::anything())
            ->willReturn([])
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;

        $this->notifier->notifyElectionVoteIsOpen($election);
    }

    public function testNotifyElectionVoteIsOpenZonedWithoutFlagUsesGetAllInZones(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: true, notifyPotentialElectorate: false);

        $this->adherentRepository
            ->expects(self::atLeastOnce())
            ->method('getAllInZones')
            ->willReturn([])
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;
        $this->adherentRepository
            ->expects(self::never())
            ->method('findRenaissanceAdherentsForElection')
        ;

        $this->notifier->notifyElectionVoteIsOpen($election);
    }

    public function testNotifyElectionVoteIsOpenZonedWithFlagOverridesGetAllInZones(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: true, notifyPotentialElectorate: true);

        $this->adherentRepository
            ->expects(self::atLeastOnce())
            ->method('findRenaissanceAdherentsForElection')
            ->with($election, false, self::anything(), self::anything())
            ->willReturn([])
        ;
        $this->adherentRepository
            ->expects(self::never())
            ->method('getAllInZones')
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;

        $this->notifier->notifyElectionVoteIsOpen($election);
    }

    public function testNotifyElectionVoteIsOpenCongressCNStillUsesDedicatedQuery(): void
    {
        // Regression check: CONGRESS_CN must keep its dedicated callback regardless of the new flag.
        $election = $this->buildElection(DesignationTypeEnum::CONGRESS_CN, withZone: false, notifyPotentialElectorate: false);

        $this->adherentRepository
            ->expects(self::atLeastOnce())
            ->method('findAllForCongressCNElection')
            ->with(false, self::anything(), self::anything())
            ->willReturn([])
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;
        $this->adherentRepository
            ->expects(self::never())
            ->method('findRenaissanceAdherentsForElection')
        ;

        $this->notifier->notifyElectionVoteIsOpen($election);
    }

    public function testNotifyVoteAnnouncementNationalWithFlagUsesPotentialElectorateRepoMethod(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: false, notifyPotentialElectorate: true);

        $this->adherentRepository
            ->expects(self::atLeastOnce())
            ->method('findRenaissanceAdherentsForElection')
            ->with($election, false, self::anything(), self::anything())
            ->willReturn([])
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;

        $this->notifier->notifyVoteAnnouncement($election);
    }

    public function testNotifyVoteAnnouncementNationalWithoutFlagUsesVotersList(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: false, notifyPotentialElectorate: false);

        $this->voterRepository
            ->expects(self::atLeastOnce())
            ->method('findForElection')
            ->willReturn([])
        ;
        $this->adherentRepository
            ->expects(self::never())
            ->method('findRenaissanceAdherentsForElection')
        ;

        $this->notifier->notifyVoteAnnouncement($election);
    }

    public function testNotifyVoteReminderNationalWithoutFlagUsesVotersList(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: false, notifyPotentialElectorate: false);

        $this->voterRepository
            ->expects(self::atLeastOnce())
            ->method('findForElection')
            ->willReturn([])
        ;
        $this->adherentRepository
            ->expects(self::never())
            ->method('findRenaissanceAdherentsForElection')
        ;
        $this->adherentRepository
            ->expects(self::never())
            ->method('getAllInZonesAndNotVoted')
        ;
        $this->notifier->notifyVoteReminder($election, Designation::NOTIFICATION_VOTE_REMINDER_1D);
    }

    public function testNotifyVoteReminderNationalWithFlagUsesPotentialElectorateRepoWithExcludeVoted(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: false, notifyPotentialElectorate: true);

        $this->adherentRepository
            ->expects(self::atLeastOnce())
            ->method('findRenaissanceAdherentsForElection')
            ->with($election, true, self::anything(), self::anything())
            ->willReturn([])
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;

        $this->notifier->notifyVoteReminder($election, Designation::NOTIFICATION_VOTE_REMINDER_1D);
    }

    public function testNotifyVoteReminderZonedWithoutFlagUsesGetAllInZonesAndNotVoted(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: true, notifyPotentialElectorate: false);

        $this->adherentRepository
            ->expects(self::atLeastOnce())
            ->method('getAllInZonesAndNotVoted')
            ->willReturn([])
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;
        $this->adherentRepository
            ->expects(self::never())
            ->method('findRenaissanceAdherentsForElection')
        ;

        $this->notifier->notifyVoteReminder($election, Designation::NOTIFICATION_VOTE_REMINDER_1D);
    }

    public function testNotifyVoteReminderZonedWithFlagOverridesGetAllInZonesAndNotVoted(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: true, notifyPotentialElectorate: true);

        $this->adherentRepository
            ->expects(self::atLeastOnce())
            ->method('findRenaissanceAdherentsForElection')
            ->with($election, true, self::anything(), self::anything())
            ->willReturn([])
        ;
        $this->adherentRepository
            ->expects(self::never())
            ->method('getAllInZonesAndNotVoted')
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;

        $this->notifier->notifyVoteReminder($election, Designation::NOTIFICATION_VOTE_REMINDER_1D);
    }

    public function testNotifyMarksNotificationAsSentAndFlushes(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: false, notifyPotentialElectorate: true);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->rebuildNotifier();

        $this->adherentRepository
            ->expects(self::atLeastOnce())
            ->method('findRenaissanceAdherentsForElection')
            ->willReturn([])
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;
        $this->entityManager
            ->expects(self::atLeastOnce())
            ->method('flush')
        ;

        self::assertFalse($election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_OPENED));

        $this->notifier->notifyElectionVoteIsOpen($election);

        self::assertTrue($election->isNotificationAlreadySent(Designation::NOTIFICATION_VOTE_OPENED));
    }

    public function testNotifyElectionVoteIsOpenIteratesAllPaginationBatches(): void
    {
        $election = $this->buildElection(DesignationTypeEnum::CONSULTATION, withZone: false, notifyPotentialElectorate: true);

        $batch1 = [$this->buildAdherent('a@b.fr')];
        $batch2 = [$this->buildAdherent('c@d.fr')];
        $this->mailer = $this->createMock(MailerService::class);
        $this->rebuildNotifier();

        $this->adherentRepository
            ->expects(self::exactly(3))
            ->method('findRenaissanceAdherentsForElection')
            ->willReturnOnConsecutiveCalls($batch1, $batch2, [])
        ;
        $this->voterRepository
            ->expects(self::never())
            ->method('findForElection')
        ;

        $this->mailer
            ->expects(self::exactly(2))
            ->method('sendMessage')
        ;

        $this->notifier->notifyElectionVoteIsOpen($election);
    }

    private function buildElection(string $type, bool $withZone, bool $notifyPotentialElectorate): Election
    {
        $designation = new Designation('Test designation');
        $designation->setType($type);
        $designation->setVoteStartDate(new \DateTime('-1 hour'));
        $designation->setVoteEndDate(new \DateTime('+1 hour'));
        $designation->targetYear = 2026;
        $designation->notifyPotentialElectorate = $notifyPotentialElectorate;

        if ($withZone) {
            $zone = new Zone(Zone::DEPARTMENT, '75', 'Paris');
            $designation->addZone($zone);
        }

        return new Election($designation, null, [new ElectionRound()]);
    }

    private function buildAdherent(string $email): Adherent
    {
        $adherent = new Adherent();
        $adherent->setEmailAddress($email);
        $adherent->tags = [TagEnum::ADHERENT];

        return $adherent;
    }
}
