<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\AdherentMandate\AdherentMandateRepository;
use App\Security\Voter\AdherentUnregistrationVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AdherentUnregistrationVoterTest extends TestCase
{
    private ?AdherentMandateRepository $adherentMandateRepository = null;
    private ?AdherentUnregistrationVoter $voter = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentMandateRepository = $this->createMock(AdherentMandateRepository::class);
        $this->voter = new AdherentUnregistrationVoter($this->adherentMandateRepository);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->adherentMandateRepository = null;
        $this->voter = null;
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, AdherentUnregistrationVoter::PERMISSION_UNREGISTER];
    }

    #[DataProvider('provideBasicAdherentCases')]
    public function testAdherentIsGrantedIfBasic(bool $granted)
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isBasicAdherent')
            ->willReturn($granted)
        ;

        if (!$granted) {
            $adherent->expects($this->once())
                ->method('isUser')
                ->willReturn(false)
            ;
        } else {
            $this->adherentMandateRepository
                ->expects($this->once())
                ->method('hasActiveMandate')
                ->with($adherent)
                ->willReturn(false)
            ;
        }

        $this->assertSame(
            $granted ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->createTokenMock(), $adherent, [AdherentUnregistrationVoter::PERMISSION_UNREGISTER])
        );
    }

    public static function provideBasicAdherentCases(): iterable
    {
        yield [true];
        yield [false];
    }

    #[DataProvider('provideUserCases')]
    public function testAdherentIsGrantedIfUser(bool $granted)
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isUser')
            ->willReturn($granted)
        ;

        $this->assertSame(
            $granted ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->createTokenMock(), $adherent, [AdherentUnregistrationVoter::PERMISSION_UNREGISTER])
        );
    }

    public static function provideUserCases(): iterable
    {
        yield [true];
        yield [false];
    }

    #[DataProvider('provideWithActiveMandateCases')]
    public function testAdherentIsGrantedIfActiveMandates(bool $granted)
    {
        $adherent = $this->createAdherentMock();

        $this->adherentMandateRepository
            ->expects($this->once())
            ->method('hasActiveMandate')
            ->with($adherent)
            ->willReturn($granted)
        ;

        $this->assertSame(
            !$granted ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->createTokenMock(), $adherent, [AdherentUnregistrationVoter::PERMISSION_UNREGISTER])
        );
    }

    public static function provideWithActiveMandateCases(): iterable
    {
        yield [true];
        yield [false];
    }

    private function createAdherentMock(): Adherent
    {
        return $this->createMock(Adherent::class);
    }

    private function createTokenMock(): TokenInterface
    {
        return $this->createMock(TokenInterface::class);
    }
}
