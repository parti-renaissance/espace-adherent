<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use PHPUnit\Framework\TestCase;

class ProcurationRequestTest extends TestCase
{
    public function testProcessAndUnprocessWithFrenchProxy()
    {
        $proxy = new ProcurationProxy();
        $proxy->setVoteCountry('FR');
        $proxy->setProxiesCount(2);

        $this->assertEmpty($proxy->getFoundRequests());
        $this->assertTrue($proxy->isFrenchRequestAvailable());
        $this->assertTrue($proxy->isForeignRequestAvailable());

        $requestFromForeignCountry = new ProcurationRequest();
        $requestFromForeignCountry->setRequestFromFrance(false);

        $requestFromForeignCountry->process($proxy);

        $this->assertCount(1, $proxy->getFoundRequests());
        $this->assertTrue($proxy->isFrenchRequestAvailable());
        $this->assertTrue($proxy->isForeignRequestAvailable());

        $requestFromFrance = new ProcurationRequest();
        $requestFromFrance->setRequestFromFrance(true);

        $requestFromFrance->process($proxy);

        $this->assertCount(2, $proxy->getFoundRequests());
        $this->assertFalse($proxy->isFrenchRequestAvailable());
        $this->assertFalse($proxy->isForeignRequestAvailable());

        $requestFromForeignCountry->unprocess();

        $this->assertCount(1, $proxy->getFoundRequests());
        $this->assertFalse($proxy->isFrenchRequestAvailable());
        $this->assertTrue(true, $proxy->isForeignRequestAvailable());

        $requestFromFrance->unprocess();

        $this->assertEmpty($proxy->getFoundRequests());
        $this->assertTrue($proxy->isFrenchRequestAvailable());
        $this->assertTrue($proxy->isForeignRequestAvailable());
    }

    public function testProcessAndUnprocessWithForeignProxy()
    {
        $proxy = new ProcurationProxy();
        $proxy->setVoteCountry('GB');
        $proxy->setProxiesCount(3);

        $this->assertEmpty($proxy->getFoundRequests());
        $this->assertTrue($proxy->isFrenchRequestAvailable());
        $this->assertTrue($proxy->isForeignRequestAvailable());

        $requestFromFrance = new ProcurationRequest();
        $requestFromFrance->setRequestFromFrance(true);

        $requestFromFrance->process($proxy);

        $this->assertCount(1, $proxy->getFoundRequests());
        $this->assertFalse($proxy->isFrenchRequestAvailable());
        $this->assertTrue($proxy->isForeignRequestAvailable());

        $requestFromForeignCountry = new ProcurationRequest();
        $requestFromForeignCountry->setRequestFromFrance(false);

        $requestFromForeignCountry->process($proxy);

        $this->assertCount(2, $proxy->getFoundRequests());
        $this->assertFalse($proxy->isFrenchRequestAvailable());
        $this->assertTrue($proxy->isForeignRequestAvailable());

        $requestFromForeignCountry2 = new ProcurationRequest();
        $requestFromForeignCountry2->setRequestFromFrance(false);

        $requestFromForeignCountry2->process($proxy);

        $this->assertCount(3, $proxy->getFoundRequests());
        $this->assertFalse($proxy->isFrenchRequestAvailable());
        $this->assertFalse($proxy->isForeignRequestAvailable());

        $requestFromFrance->unprocess();

        $this->assertCount(2, $proxy->getFoundRequests());
        $this->assertTrue($proxy->isFrenchRequestAvailable());
        $this->assertTrue($proxy->isForeignRequestAvailable());

        $requestFromForeignCountry->unprocess();

        $this->assertCount(1, $proxy->getFoundRequests());
        $this->assertTrue($proxy->isFrenchRequestAvailable());
        $this->assertTrue($proxy->isForeignRequestAvailable());

        $requestFromForeignCountry2->unprocess();

        $this->assertEmpty($proxy->getFoundRequests());
        $this->assertTrue($proxy->isFrenchRequestAvailable());
        $this->assertTrue($proxy->isForeignRequestAvailable());
    }

    /**
     * @dataProvider provideProcessTestCases
     */
    public function testProcess(
        string $proxyVoteCountry,
        string $proxiesCount,
        array $requests,
        bool $expectedFrenchAvailability,
        bool $expectedForeignAvailability
    ): void {
        $proxy = new ProcurationProxy();
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
