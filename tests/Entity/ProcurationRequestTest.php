<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use PHPUnit\Framework\TestCase;

class ProcurationRequestTest extends TestCase
{
    /**
     * @dataProvider provideTestCases
     */
    public function testFrenchRequestProcess(
        int $proxiesCount,
        string $proxyVoteCountry,
        bool $requestFromFrance,
        string $requestVoteCountry,
        bool $isFrenchRequestAvailable,
        bool $isForeignRequestAvailable
    ) {
        $proxy = new ProcurationProxy(null);
        $proxy->setVoteCountry($proxyVoteCountry);
        $proxy->setProxiesCount($proxiesCount);

        $request = new ProcurationRequest();
        $request->setVoteCountry($requestVoteCountry);
        $request->setRequestFromFrance($requestFromFrance);

        $request->process($proxy);

        $this->assertEquals($isFrenchRequestAvailable, $proxy->isFrenchRequestAvailable());
        $this->assertEquals($isForeignRequestAvailable, $proxy->isForeignRequestAvailable());
    }

    public function provideTestCases(): \Generator
    {
        // For one request max by procuration proxy
        yield 'An associated french proxy with a french request made in France should result with no more availability' => [
            1, 'FR', 1, 'FR', false, false,
        ];
        yield 'An associated french proxy with a french request not made in France should result with no more availability' => [
            1, 'FR', 0, 'FR', false, false,
        ];
        yield 'An associated french proxy with a foreign request made in France should result with no more availability' => [
            1, 'FR', 1, 'GB', false, false,
        ];
        yield 'An associated french proxy with a foreign request not made in France should result with no more availability' => [
            1, 'FR', 0, 'GB', false, false,
        ];
        yield 'An associated foreign proxy with a foreign request made in France should result with no more availability' => [
            1, 'GB', 1, 'GB', false, false,
        ];
        yield 'An associated foreign proxy with a foreign request not made in France should result with no more availability' => [
            1, 'GB', 0, 'GB', false, false,
        ];
        yield 'An associated foreign proxy with a french request made in France should result with no more availability' => [
            1, 'GB', 1, 'FR', false, false,
        ];
        yield 'An associated foreign proxy with a french request not made in France should result with no more availability' => [
            1, 'GB', 0, 'FR', false, false,
        ];

        // For two requests max by procuration proxy
        yield 'An associated french proxy with a french request made in France should result with one foreign availability' => [
            2, 'FR', 1, 'FR', false, true,
        ];
        yield 'An associated french proxy with a french request not made in France should result with one foreign availability' => [
            2, 'FR', 0, 'FR', false, true,
        ];
        yield 'An associated french proxy with a foreign request made in France should result with one foreign availability' => [
            2, 'FR', 1, 'GB', false, true,
        ];
        yield 'An associated french proxy with a foreign request not made in France should result with one foreign availability' => [
            2, 'FR', 0, 'GB', false, true,
        ];
        yield 'An associated foreign proxy with a foreign request made in France should result with one french and one foreign availability' => [
            2, 'GB', 1, 'GB', true, true,
        ];
        yield 'An associated foreign proxy with a foreign request not made in France should result with one french and one foreign availability' => [
            2, 'GB', 0, 'GB', true, true,
        ];
        yield 'An associated french proxy with a french request not made in France should result with one foreign availability' => [
            2, 'FR', 0, 'FR', false, true,
        ];
        yield 'An associated french proxy with a french request made in France should result with one foreign availability' => [
            2, 'FR', 1, 'FR', false, true,
        ];
        yield 'An associated foreign proxy with a foreign request not made in France should result with both french and foreign availability' => [
            2, 'GB', 0, 'GB', true, true,
        ];
        yield 'An associated foreign proxy with a foreign request made in France should result with both french and foreign availability' => [
            2, 'GB', 1, 'GB', true, true,
        ];

        // For three requests max by procuration proxy
        yield 'An associated french proxy with a french request made in France should result with one foreign availability' => [
            3, 'FR', 1, 'FR', false, true,
        ];
        yield 'An associated french proxy with a french request not made in France should result with one foreign availability' => [
            3, 'FR', 0, 'FR', false, true,
        ];
        yield 'An associated french proxy with a foreign request made in France should result with one foreign availability' => [
            3, 'FR', 1, 'GB', false, true,
        ];
        yield 'An associated french proxy with a foreign request not made in France should result with one foreign availability' => [
            3, 'FR', 0, 'GB', false, true,
        ];
        yield 'An associated foreign proxy with a french request made in France should result with both french and foreign availability' => [
            3, 'GB', 1, 'FR', true, true,
        ];
        yield 'An associated foreign proxy with a french request not made in France should result with both french and foreign availability' => [
            3, 'GB', 0, 'FR', true, true,
        ];
        yield 'An associated foreign proxy with a foreign request made in France should result with both french and foreign availability' => [
            3, 'GB', 1, 'GB', true, true,
        ];
        yield 'An associated foreign proxy with a foreign request not made in France should result with both french and foreign availability' => [
            3, 'GB', 0, 'GB', true, true,
        ];
    }

    public function testProcessAndUnProcessWithOneProxyCount()
    {
        $proxy = new ProcurationProxy(null);

        $request = new ProcurationRequest();
        $request->setRequestFromFrance(0);

        $request->process($proxy);

        $this->assertEquals(1, $proxy->getFoundRequests()->count());
        $this->assertEquals(false, $proxy->isFrenchRequestAvailable());
        $this->assertEquals(false, $proxy->isForeignRequestAvailable());

        $request->unprocess();

        $this->assertEquals(0, $proxy->getFoundRequests()->count());
        $this->assertEquals(true, $proxy->isFrenchRequestAvailable());
        $this->assertEquals(true, $proxy->isForeignRequestAvailable());
    }

    public function testProcessAndUnProcessWithForeignMultiProxyCount()
    {
        $proxy = new ProcurationProxy(null);
        $proxy->setProxiesCount(3);
        $proxy->setVoteCountry('GB');

        $request = new ProcurationRequest();
        $request->setRequestFromFrance(0);
        $request->setVoteCountry('GB');

        $request->process($proxy);

        $this->assertEquals(1, $proxy->getFoundRequests()->count());
        $this->assertEquals(true, $proxy->isFrenchRequestAvailable());
        $this->assertEquals(true, $proxy->isForeignRequestAvailable());

        $request = new ProcurationRequest();
        $request->setRequestFromFrance(0);
        $request->setVoteCountry('GB');

        $request->process($proxy);

        $this->assertEquals(2, $proxy->getFoundRequests()->count());
        $this->assertEquals(true, $proxy->isFrenchRequestAvailable());
        $this->assertEquals(true, $proxy->isForeignRequestAvailable());

        $request = new ProcurationRequest();
        $request->setRequestFromFrance(0);
        $request->setVoteCountry('GB');

        $request->process($proxy);

        $this->assertEquals(3, $proxy->getFoundRequests()->count());
        $this->assertEquals(false, $proxy->isFrenchRequestAvailable());
        $this->assertEquals(false, $proxy->isForeignRequestAvailable());

        $request->unprocess();

        $this->assertEquals(2, $proxy->getFoundRequests()->count());
        $this->assertEquals(true, $proxy->isFrenchRequestAvailable());
        $this->assertEquals(true, $proxy->isForeignRequestAvailable());
    }

    public function testProcessAndUnProcessWithFrenchMultiProxyCount()
    {
        $proxy = new ProcurationProxy(null);
        $proxy->setVoteCountry('GB');
        $proxy->setProxiesCount(2);

        $request = new ProcurationRequest();
        $request->setRequestFromFrance(1);
        $proxy->setVoteCountry('GB');

        $request->process($proxy);

        $this->assertEquals(1, $proxy->getFoundRequests()->count());
        $this->assertEquals(true, $proxy->isFrenchRequestAvailable());
        $this->assertEquals(true, $proxy->isForeignRequestAvailable());

        $request = new ProcurationRequest();
        $request->setRequestFromFrance(0);
        $request->setVoteCountry('GB');

        $request->process($proxy);

        $this->assertEquals(2, $proxy->getFoundRequests()->count());
        $this->assertEquals(false, $proxy->isFrenchRequestAvailable());
        $this->assertEquals(false, $proxy->isForeignRequestAvailable());
    }
}
