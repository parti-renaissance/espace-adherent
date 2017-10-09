<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Entity\Committee;
use AppBundle\Referent\ManagedAreaUtils;
use PHPUnit\Framework\TestCase;

class ManagedAreaUtilsTest extends TestCase
{
    /**
     * @dataProvider dataProviderCommittees
     */
    public function testGetCodeFromCommittee(string $country, ?string $postalCode, string $expectedCode)
    {
        $committee = $this->createMock(Committee::class);
        $committee->expects($this->any())->method('getCountry')->willReturn($country);
        $committee->expects($this->any())->method('getPostalCode')->willReturn($postalCode);

        $this->assertSame($expectedCode, ManagedAreaUtils::getCodeFromCommittee($committee));
    }

    /**
     * @dataProvider dataProviderPostalCodes
     */
    public function testGetCodeFromPostalCode(string $postalCode, string $expectedCode)
    {
        $this->assertSame($expectedCode, ManagedAreaUtils::getCodeFromPostalCode($postalCode));
    }

    /**
     * @dataProvider dataProviderCountries
     */
    public function testGetCodeFromCountry(string $country, string $expectedCode)
    {
        $this->assertSame($expectedCode, ManagedAreaUtils::getCodeFromCountry($country));
    }

    public function dataProviderCommittees()
    {
        return [
            ['CH', null, 'CH'],
            ['MC', '98000', '06'],
            ['FR', '98000', '06'],
            ['FR', '77000', '77'],
            ['FR', '75008', '75008'],
            ['FR', '20100', '2A'],
            ['FR', '20200', '2B'],
            ['FR', '97427', '974'],
            ['FR', '98890', '988'],
        ];
    }

    public function dataProviderPostalCodes()
    {
        return [
            ['77000', '77'],
            ['92500', '92'],
            ['20100', '2A'],
            ['20220', '2B'],
            ['75008', '75008'],
            ['97427', '974'],
            ['98890', '988'],
            ['98000', '06'],
        ];
    }

    public function dataProviderCountries()
    {
        return [
            ['CH', 'CH'],
            ['MC', '06'],
        ];
    }
}
