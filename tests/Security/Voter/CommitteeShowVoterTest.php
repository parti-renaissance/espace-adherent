<?php

declare(strict_types=1);

namespace Tests\App\Security\Voter;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\CommitteeShowVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\UuidInterface;

class CommitteeShowVoterTest extends AbstractAdherentVoterTestCase
{
    protected function getVoter(): AbstractAdherentVoter
    {
        return new CommitteeShowVoter();
    }

    public static function provideAnonymousCases(): iterable
    {
        yield [false, true, CommitteePermissionEnum::SHOW, fn (self $_this) => $_this->getCommitteeMock(false)];
        yield [true, false, CommitteePermissionEnum::SHOW, fn (self $_this) => $_this->getCommitteeMock(true)];
    }

    #[DataProvider('provideGroupCases')]
    public function testAdherentIsGrantedIfGroupIsApproved(bool $approved, string $attribute)
    {
        $adherent = $this->getAdherentMock(!$approved);
        $group = $this->getCommitteeMock($approved, false);

        $this->assertGrantedForAdherent($approved, !$approved, $adherent, $attribute, $group);
    }

    #[DataProvider('provideGroupCases')]
    public function testAdherentIsGrantedWhenGroupIsNotApprovedIfCreator(bool $approved, string $attribute)
    {
        $adherent = $this->getAdherentMock(!$approved);
        $group = $this->getCommitteeMock($approved, true);

        $this->assertGrantedForAdherent(true, !$approved, $adherent, $attribute, $group);
    }

    public static function provideGroupCases(): iterable
    {
        yield [true, CommitteePermissionEnum::SHOW];
        yield [false, CommitteePermissionEnum::SHOW];
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(bool $getUuidIsCalled): Adherent
    {
        $adherent = $this->createAdherentMock();

        if ($getUuidIsCalled) {
            $adherent->expects($this->once())
                ->method('getUuid')
                ->willReturn($this->createMock(UuidInterface::class))
            ;
        } else {
            $adherent->expects($this->never())
                ->method('getUuid')
            ;
        }

        return $adherent;
    }

    /**
     * @return Committee|MockObject
     */
    private function getCommitteeMock(bool $approved, ?bool $withCreator = null): Committee
    {
        $group = $this->createMock(Committee::class);

        $group->expects($this->once())
            ->method('isApproved')
            ->willReturn($approved)
        ;

        if ($approved) {
            $group->expects($this->never())
                ->method('isCreatedBy')
            ;
        } elseif (null !== $withCreator) {
            $group->expects($this->once())
                ->method('isCreatedBy')
                ->with($this->isInstanceOf(UuidInterface::class))
                ->willReturn($withCreator)
            ;
        }

        return $group;
    }
}
