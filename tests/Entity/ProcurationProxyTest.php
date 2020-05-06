<?php

namespace Tests\App\Entity;

use App\Entity\ElectionRound;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * @group procuration
 */
class ProcurationProxyTest extends TestCase
{
    public function testGetAvailableRounds(): void
    {
        $round1 = $this->createMock(ElectionRound::class);
        $round2 = $this->createMock(ElectionRound::class);

        $proxy = new ProcurationProxy();
        $proxy->setProxiesCount(2);

        $this->assertEmpty($proxy->getAvailableRounds());

        $proxy->addElectionRound($round1);
        $proxy->addElectionRound($round2);

        $this->assertCount(2, $proxy->getAvailableRounds());
        $this->assertSame([$round1, $round2], $proxy->getAvailableRounds()->toArray());

        $request1 = new ProcurationRequest();
        $request1->process($proxy);

        $this->assertCount(2, $proxy->getAvailableRounds());
        $this->assertSame([$round1, $round2], $proxy->getAvailableRounds()->toArray());

        $request2 = new ProcurationRequest();
        $request2->process($proxy);

        $this->assertEmpty($proxy->getAvailableRounds());
    }

    public function testGetAvailableRoundsWithoutRequest()
    {
        $round1 = $this->createMock(ElectionRound::class);
        $round2 = $this->createMock(ElectionRound::class);
        $round3 = $this->createMock(ElectionRound::class);

        $proxy = new ProcurationProxy();
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

        $proxy = new ProcurationProxy();
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
