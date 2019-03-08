<?php

namespace Tests\AppBundle\Security\Voter\Committee;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\Committee\CreateCommitteeVoter;
use Tests\AppBundle\Security\Voter\AbstractAdherentVoterTest;

class CreateCommitteeVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var CommitteeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $committeeRepository;

    protected function setUp(): void
    {
        $this->committeeRepository = $this->createMock(CommitteeRepository::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->committeeRepository = null;

        parent::tearDown();
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, CommitteePermissions::CREATE];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new CreateCommitteeVoter($this->committeeRepository);
    }

    public function testAdherentCannotCreateIfReferent()
    {
        $adherent = $this->getAdherentMock(true);

        $this->assertRepositoryBehavior(false);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::CREATE);
    }

    public function testAdherentCannotCreateIfAlreadyHost()
    {
        $adherent = $this->getAdherentMock(true, true);

        $this->assertRepositoryBehavior(false);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::CREATE);
    }

    public function testAdherentCannotCreateIfCommitteeIsPending()
    {
        $adherent = $this->getAdherentMock(false);

        $this->assertRepositoryBehavior(true, $adherent, false);
        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::CREATE);
    }

    public function testAdherentCanCreate()
    {
        $adherent = $this->getAdherentMock(false);

        $this->assertRepositoryBehavior(true, $adherent, true);
        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::CREATE);
    }

    /**
     * @param bool|null $isReferent
     *
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(bool $isReferent, bool $isHost = false): Adherent
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isReferent')
            ->willReturn($isReferent)
        ;

        if ($isReferent) {
            $adherent->expects($this->never())
                ->method('isHost')
            ;
        } else {
            $adherent->expects($this->once())
                ->method('isHost')
                ->willReturn($isHost)
            ;
        }

        return $adherent;
    }

    private function assertRepositoryBehavior(
        bool $isCalled,
        Adherent $adherent = null,
        bool $allowedToCreate = false
    ): void {
        if ($isCalled) {
            $this->committeeRepository->expects($this->once())
                ->method('hasCommitteeInStatus')
                ->with($adherent, Committee::STATUSES_NOT_ALLOWED_TO_CREATE_ANOTHER)
                ->willReturn(!$allowedToCreate)
            ;
        } else {
            $this->committeeRepository->expects($this->never())
                ->method('hasCommitteeInStatus')
            ;
        }
    }
}
