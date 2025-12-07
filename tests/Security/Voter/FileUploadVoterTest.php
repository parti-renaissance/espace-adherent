<?php

declare(strict_types=1);

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\UserDocument;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\FileUploadVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

class FileUploadVoterTest extends AbstractAdherentVoterTestCase
{
    public static function provideAnonymousCases(): iterable
    {
        foreach (UserDocument::ALL_TYPES as $type) {
            yield [false, true, FileUploadVoter::FILE_UPLOAD, $type];
        }
    }

    public static function provideDocumentTypes(): iterable
    {
        foreach (UserDocument::ALL_TYPES as $type) {
            yield [$type];
        }
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new FileUploadVoter($this->createMock(ScopeGeneratorResolver::class));
    }

    #[DataProvider('provideDocumentTypes')]
    public function testSimpleAdherentCanNotUploadFile(string $type)
    {
        $adherent = $this->getAdherentMock(false);

        $this->assertGrantedForAdherent(false, true, $adherent, FileUploadVoter::FILE_UPLOAD, $type);
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
}
