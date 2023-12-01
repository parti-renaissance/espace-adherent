<?php

namespace Tests\App\Procuration;

use App\Entity\ProcurationRequest;
use App\Procuration\ElectionContext;
use App\Procuration\Exception\InvalidProcurationFlowException;
use App\Procuration\ProcurationSession;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[Group('procuration')]
class ProcurationSessionTest extends TestCase
{
    /** @var SessionInterface|MockObject */
    private $session;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
    }

    protected function tearDown(): void
    {
        $this->session = null;
    }

    public function testStartRequest()
    {
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(true)
        ;
        $this->session
            ->expects($this->once())
            ->method('set')
            ->with('app_procuration_model', $this->isInstanceOf(ProcurationRequest::class))
        ;

        $procuration->startRequest();
    }

    public function testStartRequestRequiresElectionContext()
    {
        $this->expectException(InvalidProcurationFlowException::class);
        $this->expectExceptionMessage('An election context is required to start the flow.');
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(false)
        ;
        $this->session
            ->expects($this->never())
            ->method('set')
        ;

        $procuration->startRequest();
    }

    public function testEndRequest()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('app_procuration_model', true);
        $session->set('app_procuration_election_context', true);

        $this->assertTrue($session->has('app_procuration_model'));
        $this->assertTrue($session->has('app_procuration_election_context'));

        $procuration = new ProcurationSession($session);
        $procuration->endRequest();

        $this->assertFalse($session->has('app_procuration_model'));
        $this->assertFalse($session->has('app_procuration_election_context'));
    }

    public function testGetCurrentRequest()
    {
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_model')
            ->willReturn(true)
        ;
        $this->session
            ->expects($this->never())
            ->method('set')
        ;
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('app_procuration_model')
            ->willReturn(new ProcurationRequest())
        ;

        $this->assertInstanceOf(ProcurationRequest::class, $procuration->getCurrentRequest());
    }

    public function testGetCurrentRequestStartSessionIfNotStartedYet()
    {
        $procuration = new ProcurationSession($this->session);

        $series = [
            [['app_procuration_model'], false],
            [['app_procuration_election_context'], true],
        ];

        $this->session
            ->expects($this->exactly(2))
            ->method('has')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs[0], $args);

                return $expectedArgs[1];
            })
        ;
        $this->session
            ->expects($this->once())
            ->method('set')
            ->with('app_procuration_model', $this->isInstanceOf(ProcurationRequest::class))
        ;
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('app_procuration_model')
            ->willReturn(new ProcurationRequest())
        ;

        $this->assertInstanceOf(ProcurationRequest::class, $procuration->getCurrentRequest());
    }

    public function testGetElectionContext()
    {
        $context = new ElectionContext();
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(true)
        ;
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('app_procuration_election_context')
            ->willReturn(serialize($context))
        ;

        $this->assertEquals($context, $procuration->getElectionContext());
    }

    public function testGetElectionContextRequiresContext()
    {
        $this->expectException(InvalidProcurationFlowException::class);
        $this->expectExceptionMessage('No election context.');
        $procuration = new ProcurationSession($this->session);

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(false)
        ;
        $this->session
            ->expects($this->never())
            ->method('get')
        ;

        $procuration->getElectionContext();
    }

    public function testSetElectionContext()
    {
        $procuration = new ProcurationSession($this->session);

        $series = [
            ['app_procuration_model'],
            ['app_procuration_election_context'],
        ];
        $this->session
            ->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);
            })
        ;

        $series2 = [
            [['app_procuration_election_context'], fn ($param) => $this->matchesRegularExpression('~.+"MockObject_ElectionContext_.{8}":1:\{i:0;s:4:"test";}~')->evaluate($param)],
            [['app_procuration_model'], fn ($param) => $this->isInstanceOf(ProcurationRequest::class)->evaluate($param)],
        ];

        // Setting the context should reset the procuration flow
        $this->session
            ->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function (...$args) use (&$series2) {
                $expectedArgs = array_shift($series2);
                $this->assertSame($expectedArgs[0][0], $args[0]);

                $expectedArgs[1]($args[1]);
            })
        ;
        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('app_procuration_election_context')
            ->willReturn(true)
        ;

        $context = $this->createMock(ElectionContext::class);
        $context
            ->expects($this->once())
            ->method('__serialize')
            ->willReturn(['test'])
        ;

        $procuration->setElectionContext($context);
    }
}
