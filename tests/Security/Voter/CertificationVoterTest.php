<?php

declare(strict_types=1);

namespace Tests\App\Security\Voter;

use App\Adherent\Certification\CertificationPermissions;
use App\Collection\CertificationRequestCollection;
use App\Entity\Adherent;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\CertificationVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('certification')]
class CertificationVoterTest extends AbstractAdherentVoterTestCase
{
    public static function provideAnonymousCases(): iterable
    {
        yield [false, true, CertificationPermissions::CERTIFIED];
        yield [false, true, CertificationPermissions::REQUEST];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new CertificationVoter();
    }

    #[DataProvider('provideAdherentIsCertified')]
    public function testAdherentIsCertified(bool $isCertified, bool $isGranted): void
    {
        $adherent = $this->getAdherentMock($isCertified);

        $this->assertGrantedForAdherent($isGranted, true, $adherent, CertificationPermissions::CERTIFIED);
    }

    public static function provideAdherentIsCertified(): iterable
    {
        yield [true, true];
        yield [false, false];
    }

    #[DataProvider('provideAdherentCanRequest')]
    public function testAdherentCanRequest(
        bool $isCertified,
        bool $hasPendingCertificationRequest,
        bool $hasBlockedCertificationRequest,
        bool $isGranted,
    ): void {
        $adherent = $this->getAdherentMock($isCertified, $hasPendingCertificationRequest, $hasBlockedCertificationRequest);

        $this->assertGrantedForAdherent($isGranted, true, $adherent, CertificationPermissions::REQUEST);
    }

    public static function provideAdherentCanRequest(): iterable
    {
        yield [true, false, false, false];
        yield [false, true, false, false];
        yield [false, false, true, false];
        yield [false, false, false, true];
    }

    private function getAdherentMock(
        bool $isCertified,
        ?bool $hasPendingCertificationRequest = null,
        ?bool $hasBlockedCertificationRequest = null,
    ): Adherent {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isCertified')
            ->willReturn($isCertified)
        ;
        $adherent->expects($this->any())
            ->method('getBirthDate')
            ->willReturn(new \DateTime())
        ;

        if (null !== $hasPendingCertificationRequest || null !== $hasBlockedCertificationRequest) {
            $certificationRequests = $this->createMock(CertificationRequestCollection::class);

            if (null !== $hasPendingCertificationRequest) {
                $certificationRequests->expects($this->any())
                    ->method('hasPendingCertificationRequest')
                    ->willReturn($hasPendingCertificationRequest)
                ;
            }

            if (null !== $hasBlockedCertificationRequest) {
                $certificationRequests->expects($this->any())
                    ->method('hasBlockedCertificationRequest')
                    ->willReturn($hasBlockedCertificationRequest)
                ;
            }

            $adherent->expects($this->any())
                ->method('getCertificationRequests')
                ->willReturn($certificationRequests)
            ;
        }

        return $adherent;
    }
}
