<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagGenerator\AdherentStatusTagGenerator;
use App\Entity\Adherent;
use App\Repository\Contribution\PaymentRepository;
use App\Repository\DonationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdherentStatusTagGeneratorTest extends TestCase
{
    private DonationRepository&MockObject $donationRepository;
    private AdherentStatusTagGenerator $generator;

    protected function setUp(): void
    {
        $this->donationRepository = $this->createMock(DonationRepository::class);
        $this->generator = new AdherentStatusTagGenerator(
            $this->donationRepository,
            $this->createStub(PaymentRepository::class),
        );
    }

    public function testOtherPartyMembershipReturnsFlatSympathisantTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(true);

        // The "other party" short-circuit happens before any contribution lookup.
        $this->donationRepository->expects(self::never())->method('countCotisationByYearForAdherent');

        self::assertSame([TagEnum::SYMPATHISANT], $this->generator->generate($adherent, []));
    }

    public function testNoContributionAndNoSignalReturnsFlatSympathisantTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(false);

        $this->donationRepository
            ->expects(self::once())
            ->method('countCotisationByYearForAdherent')
            ->with($adherent)
            ->willReturn([])
        ;

        self::assertSame([TagEnum::SYMPATHISANT], $this->generator->generate($adherent, []));
    }

    public function testCurrentYearContributionReturnsPrimoAdherentTagNotSympathisant(): void
    {
        $year = date('Y');

        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(false);

        $this->donationRepository
            ->expects(self::once())
            ->method('countCotisationByYearForAdherent')
            ->with($adherent)
            ->willReturn([$year => 1])
        ;

        self::assertSame(
            [\sprintf(TagEnum::ADHERENT_YEAR_PRIMO_TAG_PATTERN, $year)],
            $this->generator->generate($adherent, []),
        );
    }

    public function testForcedMembershipReturnsCurrentYearAdherentTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = true;

        $this->donationRepository->expects(self::never())->method('countCotisationByYearForAdherent');

        self::assertSame(
            [TagEnum::getAdherentYearTag((int) date('Y'))],
            $this->generator->generate($adherent, []),
        );
    }
}
