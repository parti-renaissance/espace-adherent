<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagGenerator\AdherentStatusTagGenerator;
use App\Entity\Adherent;
use App\Membership\MembershipSourceEnum;
use App\Repository\Contribution\PaymentRepository;
use App\Repository\DonationRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdherentStatusTagGeneratorTest extends TestCase
{
    public function testForcedMembershipReturnsCurrentYearAdherentTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = true;

        $donationRepository = $this->createMock(DonationRepository::class);
        $donationRepository->expects(self::never())->method('countCotisationByYearForAdherent');

        self::assertSame(
            [TagEnum::getAdherentYearTag((int) date('Y'))],
            $this->createGeneratorWithDonationRepository($donationRepository)->generate($adherent, []),
        );
    }

    public function testCurrentYearContributionReturnsPrimoAdherentTagNotSympathisant(): void
    {
        $year = date('Y');

        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(false);

        $donationRepository = $this->createMock(DonationRepository::class);
        $donationRepository
            ->expects(self::once())
            ->method('countCotisationByYearForAdherent')
            ->with($adherent)
            ->willReturn([$year => 1])
        ;

        self::assertSame(
            [\sprintf(TagEnum::ADHERENT_YEAR_PRIMO_TAG_PATTERN, $year)],
            $this->createGeneratorWithDonationRepository($donationRepository)->generate($adherent, []),
        );
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

    public function testOtherPartyMembershipReturnsAutrePartiTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(true);

        // The "other party" short-circuit happens before any contribution lookup.
        $donationRepository = $this->createMock(DonationRepository::class);
        $donationRepository->expects(self::never())->method('countCotisationByYearForAdherent');

        self::assertSame(
            [TagEnum::SYMPATHISANT_AUTRE_PARTI],
            $this->createGeneratorWithDonationRepository($donationRepository)->generate($adherent, []),
        );
    }

    public function testSourceAvecvousReturnsCompteAvecvousJemengageTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(false);
        $adherent->method('getSource')->willReturn(MembershipSourceEnum::AVECVOUS);

        self::assertSame(
            [TagEnum::SYMPATHISANT_COMPTE_AVECVOUS_JEMENGAGE],
            $this->createGenerator()->generate($adherent, []),
        );
    }

    public function testSourceJemengageReturnsCompteAvecvousJemengageTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(false);
        $adherent->method('getSource')->willReturn(MembershipSourceEnum::JEMENGAGE);

        self::assertSame(
            [TagEnum::SYMPATHISANT_COMPTE_AVECVOUS_JEMENGAGE],
            $this->createGenerator()->generate($adherent, []),
        );
    }

    public function testBesoinDEuropeUserReturnsBesoinDEuropeTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(false);
        $adherent->method('getSource')->willReturn(null);
        $adherent->method('isBesoinDEuropeUser')->willReturn(true);

        self::assertSame(
            [TagEnum::SYMPATHISANT_BESOIN_D_EUROPE],
            $this->createGenerator()->generate($adherent, []),
        );
    }

    public function testSourceLegislativeReturnsEnsemble2024Tag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(false);
        $adherent->method('getSource')->willReturn(MembershipSourceEnum::LEGISLATIVE);
        $adherent->method('isBesoinDEuropeUser')->willReturn(false);

        self::assertSame(
            [TagEnum::SYMPATHISANT_ENSEMBLE2024],
            $this->createGenerator()->generate($adherent, []),
        );
    }

    public function testLegacyNonV2AccountReturnsCompteEmTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->method('isOtherPartyMembership')->willReturn(false);
        $adherent->method('getSource')->willReturn(null);
        $adherent->method('isBesoinDEuropeUser')->willReturn(false);
        $adherent->method('isV2')->willReturn(false);
        $adherent->method('getRegisteredAt')->willReturn(new \DateTime('2020-01-01'));

        self::assertSame(
            [TagEnum::SYMPATHISANT_COMPTE_EM],
            $this->createGenerator()->generate($adherent, []),
        );
    }

    public function testSignupAccountWithBirthdateReturnsMembreTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        // A filled profile (birthdate) on a signup account is the "member" leaf of the hierarchy.
        $adherent->method('getBirthdate')->willReturn(new \DateTime('1990-01-01'));

        self::assertSame([TagEnum::SYMPATHISANT_MEMBRE], $this->createGenerator()->generate($adherent, []));
    }

    public function testSignupAccountAlreadyLoggedReturnsUser(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        $adherent->method('getBirthdate')->willReturn(null);
        $adherent->method('getLastLoggedAt')->willReturn(new \DateTime('2026-05-29 10:00:00'));

        self::assertSame([TagEnum::USER], $this->createGenerator()->generate($adherent, []));
    }

    public function testSignupAccountNeverLoggedReturnsContact(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        $adherent->method('getBirthdate')->willReturn(null);
        $adherent->method('getLastLoggedAt')->willReturn(null);

        self::assertSame([TagEnum::CONTACT], $this->createGenerator()->generate($adherent, []));
    }

    public function testFallthroughReturnsAdhesionIncompleteTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->forcedMembership = false;
        $adherent->signupAccount = false;
        $adherent->method('isOtherPartyMembership')->willReturn(false);
        $adherent->method('getSource')->willReturn(null);
        $adherent->method('isBesoinDEuropeUser')->willReturn(false);
        $adherent->method('isV2')->willReturn(true);

        self::assertSame(
            [TagEnum::SYMPATHISANT_ADHESION_INCOMPLETE],
            $this->createGenerator()->generate($adherent, []),
        );
    }

    public function testSignupAccountWithLegacySourceStillReturnsMembreTag(): void
    {
        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        // The signup dispatch runs BEFORE the legacy-source branches: a signup account is never
        // reclassified by its source (the signup endpoint cannot declare one), so AVECVOUS is
        // ignored and a filled profile still yields the member leaf.
        $adherent->method('getSource')->willReturn(MembershipSourceEnum::AVECVOUS);
        $adherent->method('getBirthdate')->willReturn(new \DateTime('1990-01-01'));

        self::assertSame(
            [TagEnum::SYMPATHISANT_MEMBRE],
            $this->createGenerator()->generate($adherent, []),
        );
    }

    private function createGeneratorWithDonationRepository(
        DonationRepository&MockObject $donationRepository,
    ): AdherentStatusTagGenerator {
        return new AdherentStatusTagGenerator($donationRepository, $this->createStub(PaymentRepository::class));
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
