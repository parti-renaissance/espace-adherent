<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ProcurationProxyTest extends TestCase
{
    /**
     * @dataProvider provideTestCases
     */
    public function testGetAvailableRoundsCount(
        int $proxiesCount,
        int $requestElectionRoundsCount,
        int $proxyElectionRoundsCount,
        int $expectedAvailableElectionRoundsCount
    ) {
        $maxElectionRound = $requestElectionRoundsCount > $proxyElectionRoundsCount
            ? $requestElectionRoundsCount
            : $proxyElectionRoundsCount
        ;

        $electionRounds = [];
        for ($i = 0; $i < $maxElectionRound; ++$i) {
            array_push($electionRounds, new ElectionRound());
        }

        $request = new ProcurationRequest();
        $request->setElectionRounds(\array_slice($electionRounds, 0, $requestElectionRoundsCount));

        $proxy = new ProcurationProxy(null);
        $proxy->setProxiesCount($proxiesCount);
        $proxy->process($request);
        $proxy->setElectionRounds(\array_slice($electionRounds, 0, $proxyElectionRoundsCount));

        $this->assertCount($expectedAvailableElectionRoundsCount, $available = $proxy->getAvailableRounds());
    }

    public function provideTestCases(): \Generator
    {
        // For one request max by procuration proxy
        yield 'One election for the request and one for the proxy should not give available election' => [
            1, 1, 1, 0,
        ];
        yield 'One election for the request and two for the proxy should give one available election' => [
            1, 1, 2, 1,
        ];
        yield 'One election for the request and three for the proxy should give two available elections' => [
            1, 1, 3, 2,
        ];
        yield 'Two elections for the request and two for the proxy should not give available election' => [
            1, 2, 2, 0,
        ];
        yield 'Two elections for the request and three for the proxy should give one available election' => [
            1, 2, 3, 1,
        ];
        yield 'Three elections for the request and three for the proxy should not give available election' => [
            1, 3, 3, 0,
        ];

        // For two requests max by procuration proxy
        yield 'One election for the request and two for the proxy should give three available elections' => [
            2, 1, 2, 3,
        ];
        yield 'One election for the request and three for the proxy should give five available elections' => [
            2, 1, 3, 5,
        ];
        yield 'Two elections for the request and two for the proxy should give two available elections' => [
            2, 2, 2, 2,
        ];
        yield 'Two elections for the request and three for the proxy should give four available elections' => [
            2, 2, 3, 4,
        ];
        yield 'Three elections for the request and three for the proxy should give three available elections' => [
            2, 3, 3, 3,
        ];

        // For three requests max by procuration proxy
        yield 'One election for the request and two for the proxy should give five available elections' => [
            3, 1, 2, 5,
        ];
        yield 'One election for the request and three for the proxy should give height available elections' => [
            3, 1, 3, 8,
        ];
        yield 'Two elections for the request and two for the proxy should give four available elections' => [
            3, 2, 2, 4,
        ];
        yield 'Two elections for the request and three for the proxy should give seven available elections' => [
            3, 2, 3, 7,
        ];
        yield 'Three elections for the request and three for the proxy should give six available elections' => [
            3, 3, 3, 6,
        ];
    }

    public function testGetAvailableRounds()
    {
        $round1 = new ElectionRound();
        $round2 = new ElectionRound();
        $round3 = new ElectionRound();

        $request = new ProcurationRequest();
        $request->setElectionRounds([$round1, $round3]);

        $proxy = new ProcurationProxy(null);
        $proxy->process($request);
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
