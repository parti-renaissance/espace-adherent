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

    public function testSignupAccountNeverLoggedReturnsContact(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        $adherent->method('getLastLoggedAt')->willReturn(null);

        self::assertSame([TagEnum::CONTACT], $this->createGenerator()->generate($adherent, []));
    }

    public function testSignupAccountAlreadyLoggedReturnsUser(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        $adherent->method('getLastLoggedAt')->willReturn(new \DateTime('2026-05-29 10:00:00'));

        self::assertSame([TagEnum::USER], $this->createGenerator()->generate($adherent, []));
    }

    public function testSignupAccountWithBirthdateReturnsSympathisant(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        // birthdate wins over lastLoggedAt: a filled profile promotes to the single sympathizer/member tag.
        $adherent->method('getBirthdate')->willReturn(new \DateTime('1990-01-01'));
        $adherent->method('getLastLoggedAt')->willReturn(new \DateTime('2026-05-29 10:00:00'));

        self::assertSame([TagEnum::SYMPATHISANT], $this->createGenerator()->generate($adherent, []));
    }

    public function testSignupAccountWithCotisationReturnsAdherentTagNotContact(): void
    {
        $currentYear = (int) date('Y');

        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;

        // A signup account that cotised must be tagged as an adherent: the cotisation branch wins.
        $generator = $this->createGenerator([$currentYear => 1]);

        self::assertSame(
            [\sprintf(TagEnum::ADHERENT_YEAR_PRIMO_TAG_PATTERN, $currentYear)],
            $generator->generate($adherent, [])
        );
    }

    private function createGenerator(array $cotisationByYear = []): AdherentStatusTagGenerator
    {
        $donationRepository = $this->createStub(DonationRepository::class);
        $donationRepository->method('countCotisationByYearForAdherent')->willReturn($cotisationByYear);

        $paymentRepository = $this->createStub(PaymentRepository::class);
        $paymentRepository->method('getTotalPaymentByYearForAdherent')->willReturn([]);

        return new AdherentStatusTagGenerator($donationRepository, $paymentRepository);
    }
}
