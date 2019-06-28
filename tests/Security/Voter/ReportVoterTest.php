<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Administrator;
use AppBundle\Report\ReportPermissions;
use AppBundle\Security\Voter\ReportVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ReportVoterTest extends TestCase
{
    /**
     * @var ReportVoter
     */
    private $voter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->voter = new ReportVoter($this->authorizationChecker);
    }

    protected function tearDown(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->voter = new ReportVoter($this->authorizationChecker);
    }

    public function provideUsers(): iterable
    {
        yield [null];
        yield [$this->createMock(Adherent::class)];
    }

    /**
     * @dataProvider provideUsers
     */
    public function testGrantedWhenAuthenticatedFully($user)
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with(['IS_AUTHENTICATED_FULLY'])
            ->willReturn(true)
        ;

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, null, [ReportPermissions::REPORT])
        );
    }

    /**
     * @dataProvider provideUsers
     */
    public function testNotGrantedWhenNotAuthenticatedFully($user)
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with(['IS_AUTHENTICATED_FULLY'])
            ->willReturn(false)
        ;

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, null, [ReportPermissions::REPORT])
        );
    }

    public function testNotGrantedWhenAdministrator()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(Administrator::class))
        ;

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted')
        ;

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, null, [ReportPermissions::REPORT])
        );
    }
}
