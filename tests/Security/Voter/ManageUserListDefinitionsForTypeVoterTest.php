<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\UserListDefinitionEnum;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\ManageUserListDefinitionsForTypeVoter;
use App\UserListDefinition\UserListDefinitionPermissions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

class ManageUserListDefinitionsForTypeVoterTest extends AbstractAdherentVoterTestCase
{
    protected function getVoter(): AbstractAdherentVoter
    {
        return new ManageUserListDefinitionsForTypeVoter();
    }

    public static function provideAnonymousCases(): iterable
    {
        yield [false, true, UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE, UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE];
    }

    public function providePermissions(): iterable
    {
        yield [UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE];
    }

    public static function provideCorrectTypes(): iterable
    {
        foreach (UserListDefinitionEnum::TYPES as $type) {
            yield [$type];
        }
    }

    public function testAdherentIsNotGrantedIfTypeIsNotCorrect()
    {
        $adherent = $this->getAdherentMock();

        $this->assertGrantedForAdherent(
            false,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE,
            'failed_type'
        );
    }

    #[DataProvider('provideCorrectTypes')]
    public function testAdherentIsGrantedIfCorrectType(string $type)
    {
        $adherent = $this->getAdherentMock();

        $this->assertGrantedForAdherent(
            true,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE,
            $type
        );
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(): Adherent
    {
        return $this->createAdherentMock();
    }
}
