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
        $adherent = $this->getAdherentMock(false);

        $this->assertGrantedForAdherent(false, true, $adherent, DocumentPermissions::FILE_UPLOAD, $type);
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(?bool $isHost = null): Adherent
    {
        $adherent = $this->createAdherentMock();

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
