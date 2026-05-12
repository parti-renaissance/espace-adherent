<?php

declare(strict_types=1);

namespace Tests\App\Security\Voter;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\VotingPlatformAbleToVoteVoter;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Ramsey\Uuid\Uuid;

class VotingPlatformAbleToVoteVoterTest extends AbstractAdherentVoterTestCase
{
    private const ELECTION_UUID = '5f1d6f0a-2c2b-4f0e-9e3a-2a3b4c5d6e7f';

    private ?MockObject $voterRepository = null;
    private ?Stub $voteRepository = null;

    protected function setUp(): void
    {
        $this->voterRepository = $this->createMock(VoterRepository::class);
        $this->voteRepository = $this->createStub(VoteRepository::class);
        $this->voteRepository->method('alreadyVoted')->willReturn(false);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->voterRepository = null;
        $this->voteRepository = null;

        parent::tearDown();
    }

    public static function provideAnonymousCases(): iterable
    {
        yield [false, true, VotingPlatformAbleToVoteVoter::PERMISSION, fn (self $_this) => $_this->createElectionStub(new Designation())];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new VotingPlatformAbleToVoteVoter($this->voterRepository, $this->voteRepository);
    }

    /**
     * Consultation without a target year: the electoral body is the explicit voters list.
     * A registered adherent votes even without an up-to-date membership.
     */
    public function testConsultationVoterInTheListCanVoteWithoutUpToDateMembership(): void
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())->method('isRenaissanceSympathizer')->willReturn(false);
        $adherent->expects($this->never())->method('hasActiveMembership');

        $this->voterRepository
            ->expects($this->once())
            ->method('existsForElection')
            ->with($adherent, self::ELECTION_UUID)
            ->willReturn(true)
        ;

        $this->assertGrantedForAdherent(
            true,
            true,
            $adherent,
            VotingPlatformAbleToVoteVoter::PERMISSION,
            $this->createElectionStub($this->createConsultationDesignation())
        );
    }

    public function testConsultationVoterNotInTheListCannotVote(): void
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())->method('isRenaissanceSympathizer')->willReturn(false);

        $this->voterRepository
            ->expects($this->once())
            ->method('existsForElection')
            ->with($adherent, self::ELECTION_UUID)
            ->willReturn(false)
        ;

        $this->assertGrantedForAdherent(
            false,
            true,
            $adherent,
            VotingPlatformAbleToVoteVoter::PERMISSION,
            $this->createElectionStub($this->createConsultationDesignation())
        );
    }

    public function testConsultationSympathizerCannotVote(): void
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())->method('isRenaissanceSympathizer')->willReturn(true);

        $this->voterRepository->expects($this->never())->method('existsForElection');

        $this->assertGrantedForAdherent(
            false,
            true,
            $adherent,
            VotingPlatformAbleToVoteVoter::PERMISSION,
            $this->createElectionStub($this->createConsultationDesignation())
        );
    }

    /**
     * With a target year, the consultation keeps relying on the membership-year tag
     * (and the auto-built voters list), not on the new explicit-list check.
     */
    public function testConsultationWithTargetYearStillRequiresTheMembershipYearTag(): void
    {
        $designation = $this->createConsultationDesignation();
        $designation->targetYear = (int) date('Y');

        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())->method('isRenaissanceSympathizer')->willReturn(false);
        $adherent->expects($this->once())->method('hasTag')->willReturn(false);

        $this->voterRepository->expects($this->never())->method('existsForElection');

        $this->assertGrantedForAdherent(
            false,
            true,
            $adherent,
            VotingPlatformAbleToVoteVoter::PERMISSION,
            $this->createElectionStub($designation)
        );
    }

    private function createConsultationDesignation(): Designation
    {
        $designation = new Designation('Consultation de test');
        $designation->setType(DesignationTypeEnum::CONSULTATION);

        return $designation;
    }

    private function createElectionStub(Designation $designation): Election&Stub
    {
        $election = $this->createStub(Election::class);
        $election->method('isVotePeriodActive')->willReturn(true);
        $election->method('getCurrentRound')->willReturn($this->createStub(ElectionRound::class));
        $election->method('getDesignation')->willReturn($designation);
        $election->method('getUuid')->willReturn(Uuid::fromString(self::ELECTION_UUID));

        return $election;
    }
}
