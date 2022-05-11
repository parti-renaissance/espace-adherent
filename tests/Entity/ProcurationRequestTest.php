<?php

namespace Tests\App\Entity;

use App\Entity\ElectionRound;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use PHPUnit\Framework\TestCase;

/**
 * @group procuration
 */
class ProcurationRequestTest extends TestCase
{
    public function testProcessAndUnprocessWithFrenchProxy()
    {
        $proxy = new ProcurationProxy();
        $proxy->setVoteCountry('FR');
        $proxy->setProxiesCount(2);

        $this->assertEmpty($proxy->getFoundRequests());
        $this->assertEmpty($proxy->getElectionRounds());

        $electionRound = new ElectionRound();

        $requestFromForeignCountry = new ProcurationRequest();
        $requestFromForeignCountry->setRequestFromFrance(false);
        $requestFromForeignCountry->addElectionRound($electionRound);

        $proxy->addElectionRound($electionRound);
        $ppElectionRound = $proxy->findProcurationProxyElectionRoundBy($electionRound);

        $this->assertTrue($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());

        $requestFromForeignCountry->process($proxy);

        $this->assertCount(1, $proxy->getFoundRequests());
        $this->assertCount(1, $proxy->getElectionRounds());
        $this->assertTrue($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());

        $requestFromFrance = new ProcurationRequest();
        $requestFromFrance->setRequestFromFrance(true);
        $requestFromFrance->addElectionRound($electionRound);

        $requestFromFrance->process($proxy);

        $this->assertCount(2, $proxy->getFoundRequests());
        $this->assertFalse($ppElectionRound->isFrenchRequestAvailable());
        $this->assertFalse($ppElectionRound->isForeignRequestAvailable());

        $requestFromForeignCountry->unprocess();

        $this->assertCount(1, $proxy->getFoundRequests());
        $this->assertFalse($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());

        $requestFromFrance->unprocess();

        $this->assertEmpty($proxy->getFoundRequests());
        $this->assertTrue($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());
    }

    public function testProcessAndUnprocessWithForeignProxy()
    {
        $electionRound = new ElectionRound();
        $proxy = new ProcurationProxy();
        $proxy->setVoteCountry('GB');
        $proxy->setProxiesCount(3);

        $this->assertEmpty($proxy->getFoundRequests());

        $requestFromFrance = new ProcurationRequest();
        $requestFromFrance->setRequestFromFrance(true);
        $requestFromFrance->addElectionRound($electionRound);

        $proxy->addElectionRound($electionRound);
        $ppElectionRound = $proxy->findProcurationProxyElectionRoundBy($electionRound);

        $this->assertTrue($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());

        $requestFromFrance->process($proxy);

        $this->assertCount(1, $proxy->getFoundRequests());
        $this->assertFalse($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());

        $requestFromForeignCountry = new ProcurationRequest();
        $requestFromForeignCountry->setRequestFromFrance(false);
        $requestFromForeignCountry->addElectionRound($electionRound);

        $requestFromForeignCountry->process($proxy);

        $this->assertCount(2, $proxy->getFoundRequests());
        $this->assertFalse($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());

        $requestFromForeignCountry2 = new ProcurationRequest();
        $requestFromForeignCountry2->setRequestFromFrance(false);
        $requestFromForeignCountry2->addElectionRound($electionRound);

        $requestFromForeignCountry2->process($proxy);

        $this->assertCount(3, $proxy->getFoundRequests());
        $this->assertFalse($ppElectionRound->isFrenchRequestAvailable());
        $this->assertFalse($ppElectionRound->isForeignRequestAvailable());

        $requestFromFrance->unprocess();

        $this->assertCount(2, $proxy->getFoundRequests());
        $this->assertTrue($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());

        $requestFromForeignCountry->unprocess();

        $this->assertCount(1, $proxy->getFoundRequests());
        $this->assertTrue($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());

        $requestFromForeignCountry2->unprocess();

        $this->assertEmpty($proxy->getFoundRequests());
        $this->assertTrue($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());
    }

    /**
     * @dataProvider provideProcessTestCases
     */
    public function testProcess(
        string $proxyVoteCountry,
        int $proxiesCount,
        array $requests,
        bool $expectedFrenchAvailability,
        bool $expectedForeignAvailability
    ): void {
        $electionRound = new ElectionRound();
        $proxy = new ProcurationProxy();
        $proxy->setVoteCountry($proxyVoteCountry);
        $proxy->setProxiesCount($proxiesCount);
        $proxy->addElectionRound($electionRound);
        $ppElectionRound = $proxy->findProcurationProxyElectionRoundBy($electionRound);

        $this->assertTrue($ppElectionRound->isFrenchRequestAvailable());
        $this->assertTrue($ppElectionRound->isForeignRequestAvailable());

        foreach ($requests as $requestData) {
            $request = new ProcurationRequest();
            $request->setRequestFromFrance($requestData);
            $request->addElectionRound($electionRound);

            $request->process($proxy);
        }

        $this->assertEquals($expectedFrenchAvailability, $ppElectionRound->isFrenchRequestAvailable());
        $this->assertEquals($expectedForeignAvailability, $ppElectionRound->isForeignRequestAvailable());
    }

    public function provideProcessTestCases(): \Generator
    {
        yield 'Proxy from france, with 1 slot and only one request from france: should not be available for french nor foreign extra requests' => [
            'FR', 1, [true], false, false,
        ];
        yield 'Proxy from france, with 1 slot and only one request not from france: should not be available for french nor foreign extra requests' => [
            'FR', 1, [false], false, false,
        ];
        yield 'Proxy from france, with 2 slots and only one request from france: should not be available for french requests, but available for foreign extra requests' => [
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
        yield 'Proxy from france, with 2 slots and two requests from france: should not be available for french nor foreign extra requests' => [
            'FR', 2, [true, true], false, false,
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
        yield 'Proxy from foreign country, with 2 slots and only one request from france: should not be available for french requests, but available for foreign extra requests' => [
            'GB', 2, [true], false, true,
        ];
        yield 'Proxy from foreign country, with 2 slots and only one request from foreign country: should be available for french and foreign extra requests' => [
            'GB', 2, [false], true, true,
        ];
        yield 'Proxy from foreign country, with 2 slots and two requests (one from france and one from foreign country): should not be available for french nor foreign extra requests' => [
            'GB', 2, [true, false], false, false,
        ];
        yield 'Same scenario as above, but changing requests order: should be the same result as above.' => [
            'GB', 2, [false, true], false, false,
        ];
        yield 'Proxy from foreign country, with 2 slots and two requests from france: should not be available for french nor foreign extra requests' => [
            'GB', 2, [true, true], false, false,
        ];
        yield 'Proxy from foreign country, with 2 slots and two requests from foreign country: should not be available for french nor foreign extra requests' => [
            'GB', 2, [false, false], false, false,
        ];
        yield 'Proxy from foreign country, with 3 slots and only one request from france: should not be available for french requests, but available for foreign extra requests' => [
            'GB', 3, [true], false, true,
        ];
        yield 'Proxy from foreign country, with 3 slots and only one request from foreign country: should be available for french and foreign extra requests' => [
            'GB', 3, [false], true, true,
        ];
        yield 'Proxy from foreign country, with 3 slots and two requests (one from france and one from foreign country): should not be available for french requets, but available for foreign extra request' => [
            'GB', 3, [true, false], false, true,
        ];
        yield 'Same scenario as above, but changing requests order: should be the same result as above..' => [
            'GB', 3, [false, true], false, true,
        ];
        yield 'Proxy from foreign country, with 3 slots and two requests from foreign country: should be available for french and foreign extra requests' => [
            'GB', 3, [false, false], true, true,
        ];
        yield 'Proxy from foreign country, with 3 slots and two requests from france: should not be available for french extra request, but is available for foreign extra request' => [
            'GB', 3, [true, true], false, true,
        ];
        yield 'Proxy from foreign country, with 3 slots and 3 requests (one from france and two from foreign countries): should not be available for french nor foreign extra requests' => [
            'GB', 3, [true, false, false], false, false,
        ];
        yield 'Same scenario as above, but changing requests order: should be the same result as above...' => [
            'GB', 3, [false, true, false], false, false,
        ];
        yield 'Same scenario as above, but changing requests order: should be the same result as above....' => [
            'GB', 3, [false, false, true], false, false,
        ];
        yield 'Proxy from foreign country, with 3 slots and three requests from foreign country: should not be available for french nor foreign extra requests' => [
            'GB', 3, [false, false, false], false, false,
        ];
    }
}
