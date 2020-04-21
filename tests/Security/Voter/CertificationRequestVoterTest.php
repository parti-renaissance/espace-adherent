<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\Adherent\CertificationPermissions;
use AppBundle\Collection\CertificationRequestCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\CertificationRequestVoter;

/**
 * @group certification
 */
class CertificationRequestVoterTest extends AbstractAdherentVoterTest
{
    public function provideAnonymousCases(): iterable
    {
        yield [false, true, CertificationPermissions::REQUEST];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new CertificationRequestVoter();
    }

    /**
     * @dataProvider provideAdherentCanRequest
     */
    public function testAdherentCanRequest(
        bool $isCertified,
        bool $hasPendingCertificationRequest,
        bool $isGranted
    ): void {
        $adherent = $this->getAdherentMock($isCertified, $hasPendingCertificationRequest);

        $this->assertGrantedForAdherent($isGranted, true, $adherent, CertificationPermissions::REQUEST);
    }

    public function provideAdherentCanRequest(): iterable
    {
        yield [true, false, false];
        yield [false, true, false];
        yield [false, false, true];
    }

    private function getAdherentMock(bool $isCertified, bool $hasPendingCertificationRequest): Adherent
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->any())
            ->method('isCertified')
            ->willReturn($isCertified)
        ;

        $certificationRequests = $this->createMock(CertificationRequestCollection::class);
        $certificationRequests->expects($this->any())
            ->method('hasPendingCertificationRequest')
            ->willReturn($hasPendingCertificationRequest)
        ;
        $adherent->expects($this->any())
            ->method('getCertificationRequests')
            ->willReturn($certificationRequests)
        ;

        return $adherent;
    }
}
