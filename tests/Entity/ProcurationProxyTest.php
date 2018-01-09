<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ProcurationProxyTest extends TestCase
{
    public function testGetAvailableRounds()
    {
        $round1 = $this->createMock(ElectionRound::class);
        $round2 = $this->createMock(ElectionRound::class);
        $round3 = $this->createMock(ElectionRound::class);

        $request = $this->createMock(ProcurationRequest::class);
        $request
            ->expects($this->exactly(3))
            ->method('hasElectionRound')
            ->withConsecutive($round1, $round2, $round3)
            ->willReturnOnConsecutiveCalls(true, false, true)
        ;

        $proxy = new ProcurationProxy(null);
        $proxy->setFoundRequest($request);
        $proxy->setElectionRounds([$round1, $round2, $round3]);

        $this->assertCount(1, $available = $proxy->getAvailableRounds());
        $this->assertSame($round2, $available->first());
    }

    public function testGetAvailableRoundsWithoutRequest()
    {
        $round1 = $this->createMock(ElectionRound::class);
        $round2 = $this->createMock(ElectionRound::class);
        $round3 = $this->createMock(ElectionRound::class);

        $proxy = new ProcurationProxy(null);
        $proxy->setElectionRounds([$round1, $round2, $round3]);

        $this->assertEquals(new ArrayCollection([$round1, $round2, $round3]), $proxy->getAvailableRounds());
    }

    public function testMatchesRequest()
    {
        $voteCountry = 'FR';
        $votePostalCode = '06000';
        $round1 = $this->createMock(ElectionRound::class);
        $round2 = $this->createMock(ElectionRound::class);

        /* Different vote country */
        $request = $this->createMock(ProcurationRequest::class);
        $request
            ->expects($this->once())
            ->method('getVoteCountry')
            ->willReturn(null)
        ;
        $request
            ->expects($this->never())
            ->method('getVotePostalCode')
        ;
        $request
            ->expects($this->never())
            ->method('getElectionRounds')
        ;

        $proxy = new ProcurationProxy(null);
        $proxy->setVoteCountry($voteCountry);
        $proxy->setVotePostalCode($votePostalCode);

        $this->assertFalse($proxy->matchesRequest($request));

        /* Same vote country and different vote postal code */
        $request = $this->createMock(ProcurationRequest::class);
        $request
            ->expects($this->once())
            ->method('getVoteCountry')
            ->willReturn($voteCountry)
        ;
        $request
            ->expects($this->once())
            ->method('getVotePostalCode')
            ->willReturn(null)
        ;
        $request
            ->expects($this->never())
            ->method('getElectionRounds')
        ;

        $this->assertFalse($proxy->matchesRequest($request));

        /* Same vote country and same vote postal code, no rounds */
        $request = $this->createMock(ProcurationRequest::class);
        $request
            ->expects($this->once())
            ->method('getVoteCountry')
            ->willReturn($voteCountry)
        ;
        $request
            ->expects($this->once())
            ->method('getVotePostalCode')
            ->willReturn(substr($votePostalCode, 0, 2))
        ;
        $request
            ->expects($this->once())
            ->method('getElectionRounds')
            ->willReturn(new ArrayCollection())
        ;

        $this->assertTrue($proxy->matchesRequest($request));

        /* Same vote country and same vote postal code, different rounds */
        $request = $this->createMock(ProcurationRequest::class);
        $request
            ->expects($this->once())
            ->method('getVoteCountry')
            ->willReturn($voteCountry)
        ;
        $request
            ->expects($this->once())
            ->method('getVotePostalCode')
            ->willReturn(substr($votePostalCode, 0, 2))
        ;
        $request
            ->expects($this->once())
            ->method('getElectionRounds')
            ->willReturn(new ArrayCollection([$round1, $round2]))
        ;

        $proxy->setElectionRounds([$round1]);

        $this->assertFalse($proxy->matchesRequest($request));

        /* Same vote country and same vote postal code, same rounds */
        $request = $this->createMock(ProcurationRequest::class);
        $request
            ->expects($this->once())
            ->method('getVoteCountry')
            ->willReturn($voteCountry)
        ;
        $request
            ->expects($this->once())
            ->method('getVotePostalCode')
            ->willReturn(substr($votePostalCode, 0, 2))
        ;
        $request
            ->expects($this->once())
            ->method('getElectionRounds')
            ->willReturn(new ArrayCollection([$round1]))
        ;

        $proxy->setElectionRounds([$round1, $round2]);

        $this->assertTrue($proxy->matchesRequest($request));
    }
}
