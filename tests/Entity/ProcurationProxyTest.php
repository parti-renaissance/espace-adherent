<?php

namespace Tests\App\Entity;

use App\Entity\ElectionRound;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationProxyElectionRound;
use App\Entity\ProcurationRequest;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('procuration')]
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
        $this->assertSame([$round1, $round2], array_map(function (ProcurationProxyElectionRound $ppElectionRound) {
            return $ppElectionRound->getElectionRound();
        }, $proxy->getAvailableRounds()->toArray()));

        $request1 = new ProcurationRequest();
        $request1->addElectionRound($round2);
        $request1->setRequestFromFrance(false);
        $request1->process($proxy);

        $this->assertCount(2, $proxy->getAvailableRounds());
        $this->assertSame([$round1, $round2], array_map(function (ProcurationProxyElectionRound $ppElectionRound) {
            return $ppElectionRound->getElectionRound();
        }, $proxy->getAvailableRounds()->toArray()));

        $request2 = new ProcurationRequest();
        $request2->addElectionRound($round2);
        $request2->setRequestFromFrance(true);
        $request2->process($proxy);

        $this->assertSame([$round1], array_map(function (ProcurationProxyElectionRound $ppElectionRound) {
            return $ppElectionRound->getElectionRound();
        }, $proxy->getAvailableRounds()->toArray()));

        $request3 = new ProcurationRequest();
        $request3->addElectionRound($round1);
        $request3->setRequestFromFrance(true);
        $request3->process($proxy);

        $this->assertSame([$round1], array_map(function (ProcurationProxyElectionRound $ppElectionRound) {
            return $ppElectionRound->getElectionRound();
        }, $proxy->getAvailableRounds()->toArray()));

        $request4 = new ProcurationRequest();
        $request4->addElectionRound($round1);
        $request4->setRequestFromFrance(false);
        $request4->process($proxy);

        $this->assertEmpty($proxy->getAvailableRounds());
    }

    public function testGetAvailableRoundsWithoutRequest()
    {
        $round1 = $this->createMock(ElectionRound::class);
        $round2 = $this->createMock(ElectionRound::class);
        $round3 = $this->createMock(ElectionRound::class);

        $proxy = new ProcurationProxy();
        $proxy->setElectionRounds([$round1, $round2, $round3]);

        $this->assertSame([$round1, $round2, $round3], array_map(function (ProcurationProxyElectionRound $ppElectionRound) {
            return $ppElectionRound->getElectionRound();
        }, $proxy->getAvailableRounds()->toArray()));
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
            ->expects($this->any())
            ->method('getElectionRounds')
            ->willReturn(new ArrayCollection([$round1]))
        ;

        $this->assertFalse($proxy->matchesRequest($request));

        /* Same vote country and same vote postal code, different rounds */
        $request = $this->createMock(ProcurationRequest::class);
        $request
            ->expects($this->once())
            ->method('getVoteCountry')
            ->willReturn($voteCountry)
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
            ->method('getElectionRounds')
            ->willReturn(new ArrayCollection([$round1, $round2]))
        ;

        $proxy->setElectionRounds([$round1, $round2]);

        $this->assertTrue($proxy->matchesRequest($request));
    }
}
