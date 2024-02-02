<?php

namespace Tests\App\Security\Voter;

use App\Documents\DocumentPermissions;
use App\Entity\Adherent;
use App\Entity\UserDocument;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\FileUploadVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FileUploadVoterTest extends AbstractAdherentVoterTestCase
{
    public static function provideAnonymousCases(): iterable
    {
        foreach (UserDocument::ALL_TYPES as $type) {
            yield [false, true, DocumentPermissions::FILE_UPLOAD, $type];
        }
    }

    public static function provideDocumentTypes(): iterable
    {
        return static::getUserDocumentTypesDataProvider();
    }

    public static function provideReferentRights(): iterable
    {
        return static::getUserDocumentTypesDataProvider([
            UserDocument::TYPE_REFERENT,
            UserDocument::TYPE_EVENT,
        ]);
    }

    public static function provideHostRights(): iterable
    {
        return static::getUserDocumentTypesDataProvider([
            UserDocument::TYPE_COMMITTEE_CONTACT,
            UserDocument::TYPE_COMMITTEE_FEED,
            UserDocument::TYPE_EVENT,
        ]);
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new FileUploadVoter(
            $this->createConfiguredMock(AuthorizationCheckerInterface::class, ['isGranted' => false]),
            $this->createMock(ScopeGeneratorResolver::class)
        );
    }

    #[DataProvider('provideDocumentTypes')]
    public function testSimpleAdherentCanNotUploadFile(string $type)
    {
        $adherent = $this->getAdherentMock(false, false);

        $this->assertGrantedForAdherent(false, true, $adherent, DocumentPermissions::FILE_UPLOAD, $type);
    }

    #[DataProvider('provideReferentRights')]
    public function testReferentRights(string $type, bool $granted)
    {
        $adherent = $this->getAdherentMock(true, false);

        $this->assertGrantedForAdherent($granted, true, $adherent, DocumentPermissions::FILE_UPLOAD, $type);
    }

    #[DataProvider('provideHostRights')]
    public function testHostRights(string $type, bool $granted)
    {
        $adherent = $this->getAdherentMock(false, true);

        $this->assertGrantedForAdherent($granted, true, $adherent, DocumentPermissions::FILE_UPLOAD, $type);
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(?bool $isReferent = null, ?bool $isHost = null): Adherent
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

    private static function getUserDocumentTypesDataProvider(array $allowedTypes = []): \Generator
    {
        foreach (UserDocument::ALL_TYPES as $type) {
            yield [$type, \in_array($type, $allowedTypes)];
        }
    }
}
