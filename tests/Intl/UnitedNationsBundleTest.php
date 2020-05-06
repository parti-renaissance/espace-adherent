<?php

namespace Tests\App\Intl;

use App\Intl\UnitedNationsBundle;
use PHPUnit\Framework\TestCase;

class UnitedNationsBundleTest extends TestCase
{
    public function testGetCountries()
    {
        $countries = UnitedNationsBundle::getCountries('fr');

        $this->assertArrayHasKey('FR', $countries);
        $this->assertArrayNotHasKey('GP', $countries);
        $this->assertArrayNotHasKey('RE', $countries);
    }
}
