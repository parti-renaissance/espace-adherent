<?php

namespace Tests\App\Security\Voter\Committee;

use App\Documents\DocumentPermissions;
use App\Entity\Adherent;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\FileUploadVoter;
use Tests\App\Security\Voter\AbstractAdherentVoterTest;

class FileUploadVoterTest extends AbstractAdherentVoterTest
{
    public function provideAnonymousCases(): iterable
    {
        yield [false, true, DocumentPermissions::FILE_UPLOAD, 'committee_contact'];
        yield [false, true, DocumentPermissions::FILE_UPLOAD, 'committee_feed'];
        yield [false, true, DocumentPermissions::FILE_UPLOAD, 'event'];
        yield [false, true, DocumentPermissions::FILE_UPLOAD, 'referent'];
    }

    public function provideDocumentTypes(): iterable
    {
        yield ['committee_contact'];
        yield ['committee_feed'];
        yield ['event'];
        yield ['referent'];
    }

    public function provideReferentRights(): iterable
    {
        yield ['committee_contact', false];
        yield ['committee_feed', false];
        yield ['event', true];
        yield ['referent', true];
    }

    public function provideHostRights(): iterable
    {
        yield ['committee_contact', true];
        yield ['committee_feed', true];
        yield ['event', true];
        yield ['referent', false];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new FileUploadVoter();
    }

    /**
     * @dataProvider provideDocumentTypes
     */
    public function testSimpleAdherentCanNotUploadFile(string $type)
    {
        $adherent = $this->getAdherentMock(false, false);

        $this->assertGrantedForAdherent(false, true, $adherent, DocumentPermissions::FILE_UPLOAD, $type);
    }

    /**
     * @dataProvider provideReferentRights
     */
    public function testReferentRights(string $type, bool $granted)
    {
        $adherent = $this->getAdherentMock(true, false);

        $this->assertGrantedForAdherent($granted, true, $adherent, DocumentPermissions::FILE_UPLOAD, $type);
    }

    /**
     * @dataProvider provideHostRights
     */
    public function testHostRights(string $type, bool $granted)
    {
        $adherent = $this->getAdherentMock(false, true);

        $this->assertGrantedForAdherent($granted, true, $adherent, DocumentPermissions::FILE_UPLOAD, $type);
    }

    /**
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(bool $isReferent = null, bool $isHost = null): Adherent
    {
        $adherent = $this->createAdherentMock();

        if (null !== $isReferent) {
            $adherent
                ->expects(self::any())
                ->method('isReferent')
                ->willReturn($isReferent)
            ;
        }

        if (null !== $isHost) {
            $adherent
                ->expects(self::any())
                ->method('isHost')
                ->willReturn($isHost)
            ;
        }

        return $adherent;
    }
}
