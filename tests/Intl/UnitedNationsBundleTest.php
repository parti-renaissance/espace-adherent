<?php

namespace Tests\AppBundle\Intl;

use AppBundle\Intl\UnitedNationsBundle;

class UnitedNationsBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCountries()
    {
        $countries = UnitedNationsBundle::getCountries('fr');

        $this->assertArrayHasKey('FR', $countries);
        $this->assertArrayNotHasKey('GP', $countries);
        $this->assertArrayNotHasKey('RE', $countries);
    }
}
