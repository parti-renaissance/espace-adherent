<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ReferentManagedArea;
use App\Entity\ReferentTag;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\ManageUserListDefinitionElectedRepresentativeVoter;
use App\UserListDefinition\UserListDefinitionPermissions;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ManageUserListDefinitionElectedRepresentativeVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var ElectedRepresentativeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $electedRepresentativeRepository;

    /** @var RequestStack */
    private $requestStack;

    protected function setUp(): void
    {
        $this->electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->method('getMasterRequest')->willReturn(new Request());

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->electedRepresentativeRepository = null;
        $this->requestStack = null;

        parent::tearDown();
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new ManageUserListDefinitionElectedRepresentativeVoter($this->electedRepresentativeRepository, $this->requestStack);
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER, $this->createMock(ElectedRepresentative::class)];
    }

    public function providePermissions(): iterable
    {
        yield [UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER];
    }

    public function testAdherentIsNotGrantedIfNotReferent()
    {
        $adherent = $this->getAdherentMock(true, false);
        $electedRepresentative = $this->createMock(ElectedRepresentative::class);

        $this->assertGrantedForAdherent(
            false,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER,
            $electedRepresentative
        );
    }

    public function testAdherentIsNotGrantedIfNotInReferentArea()
    {
        $tags = [new ReferentTag()];
        $adherent = $this->getAdherentMock(true, true, $tags);
        $electedRepresentative = $this->createMock(ElectedRepresentative::class);

        $this->electedRepresentativeRepository->expects($this->once())
            ->method('isInReferentManagedArea')
            ->with($electedRepresentative, $tags)
            ->willReturn(true)
        ;

        $this->assertGrantedForAdherent(
            true,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER,
            $electedRepresentative
        );
    }

    public function testAdherentIsGrantedIfReferent()
    {
        $tags = [new ReferentTag()];
        $adherent = $this->getAdherentMock(true, true, $tags);
        $electedRepresentative = $this->createMock(ElectedRepresentative::class);

        $this->electedRepresentativeRepository->expects($this->once())
            ->method('isInReferentManagedArea')
            ->with($electedRepresentative, $tags)
            ->willReturn(true)
        ;

        $this->assertGrantedForAdherent(
            true,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER,
            $electedRepresentative
        );
    }

    /**
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(
        bool $isReferentCalled = true,
        bool $isReferent = true,
        array $referentTags = []
    ): Adherent {
        $adherent = $this->createAdherentMock();

        if ($referentTags) {
            $referentManagedArea = $this->createMock(ReferentManagedArea::class);
            $referentManagedArea->expects($this->once())
                ->method('getTags')
                ->willReturn(new ArrayCollection($referentTags))
            ;

            $adherent->expects($this->once())
                ->method('getManagedArea')
                ->willReturn($referentManagedArea)
            ;
        }

        $adherent->expects($isReferentCalled ? $this->once() : $this->never())
            ->method('isReferent')
            ->willReturn($isReferent)
        ;

        return $adherent;
    }
}
