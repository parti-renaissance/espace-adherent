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

    /**
     * @group debug
     * @dataProvider provideProcessTestCases
     */
    public function testProcess(
        string $proxyVoteCountry,
        string $proxiesCount,
        array $requests,
        bool $expectedFrenchAvailability,
        bool $expectedForeignAvailability
    ): void {
        $proxy = new ProcurationProxy(null);
        $proxy->setVoteCountry($proxyVoteCountry);
        $proxy->setProxiesCount($proxiesCount);

        foreach ($requests as $requestData) {
            $request = new ProcurationRequest();
            $request->setRequestFromFrance($requestData);

            $request->process($proxy);
        }

        $this->assertEquals($expectedFrenchAvailability, $proxy->isFrenchRequestAvailable());
        $this->assertEquals($expectedForeignAvailability, $proxy->isForeignRequestAvailable());
    }

    public function provideProcessTestCases(): \Generator
    {
        yield 'Proxy from france, with 1 slot and only one request from france: should not be available for french nor foreign extra requests' => [
            'FR', 1, [true], false, false,
        ];
        yield 'Proxy from france, with 1 slot and only one request not from france: should not be available for french nor foreign extra requests' => [
            'FR', 1, [false], false, false,
        ];
        yield 'Proxy from france, with 2 slots and only one request from france: should not be available for french extra requests, but is available for foreign extra requests' => [
            'FR', 2, [true], false, true,
        ];
        yield 'Proxy from france, with 2 slots and only one request from a foreign country: should be available for french extra request, but not foreign extra requests' => [
            'FR', 2, [false], true, true,
        ];
        yield 'Proxy from france, with 2 slots and two requests (one from france and one from foreign country): should not be available for french nor foreign extra requests' => [
            'FR', 2, [true, false], false, false,
        ];
        yield 'Same scenario as above, but changing requests order: should be the same result as above' => [
            'FR', 2, [false, true], false, false,
        ];
        yield 'Proxy from france, with 2 slots and two requests from foreign countries: shouuld not be available for french nor foreign extra requests' => [
            'FR', 2, [false, false], false, false,
        ];
        yield 'Proxy from foreign country, with 1 slot and only one request from france: should not be available for french nor foreign extra requests' => [
            'GB', 1, [true], false, false,
        ];
        yield 'Proxy from foreign country, with 1 slot and only one request from foreign country: should not be available for french nor foreign extra requests' => [
            'GB', 1, [false], false, false,
        ];
        yield 'Proxy from foreign country, with 2 slots and only one request from france: should not be available for french extra requests, but is available for foreign extra requests' => [
            'GB', 2, [true], false, true,
        ];
        yield 'Proxy from foreign country, with 2 slots and only one request from foreign country: should be available for french and foreign extra requests' => [
            'GB', 2, [false], true, true,
        ];
        yield 'Proxy from foreign country, with 2 slots and two requests (one from france and one from foreign country): should not be available for french nor foreign extra requests' => [
            'GB', 2, [true, false], false, false,
        ];
        yield 'Same scenario as above, but changing requests order: should be the same result as above..' => [
            'GB', 2, [false, true], false, false,
        ];
        yield 'Proxy from foreign country, with 2 slots and two requests from foreign country: should not be available for french nor foreign extra requests' => [
            'GB', 2, [false, false], false, false,
        ];
        yield 'Proxy from foreign country, with 3 slots and only one request from france: should not be available for french extra requests, but is available for foreign extra requests' => [
            'GB', 3, [true], false, true,
        ];
        yield 'Proxy from foreign country, with 3 slots and only one request from foreign country: should be available for french and foreign extra requests' => [
            'GB', 3, [false], true, true,
        ];
        yield 'Proxy from foreign country, with 3 slots and two requests (one from france and one from foreign country): should not be available for french extra request but should be available for foreign extra requests' => [
            'GB', 3, [true, false], false, true,
        ];
        yield 'Same scenario as above, but changing requests order: should be the same result as above' => [
            'GB', 3, [false, true], false, true,
        ];
        yield 'Proxy from foreign country, with 3 slots and two requests from foreign country: should be available for french and foreign extra requests' => [
            'GB', 3, [false, false], true, true,
        ];
        yield 'Proxy from foreign country, with 3 slots and 3 requests (one from france and two from foreign countries): should not be available for french nor foreign extra requests' => [
            'GB', 3, [true, false, false], false, false,
        ];
        yield 'Same scenario as above, but changing requests order: should be the same result as above' => [
            'GB', 3, [false, true, false], false, false,
        ];
        yield 'Same scenario as above, but changing requests order: should be the same result as above' => [
            'GB', 3, [false, false, true], false, false,
        ];
        yield 'Proxy from foreign country, with 3 slots and three requests from foreign country: should not be available for french nor foreign extra requests' => [
            'GB', 3, [false, false, false], false, false,
        ];
    }
}
