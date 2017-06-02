<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\ProcurationRequest;

class ProcurationRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testGetElectionsRoundsCount()
    {
        $request = new ProcurationRequest();
        $this->assertSame(0, $request->getElectionsRoundsCount());

        $request->setElectionPresidentialFirstRound(true);
        $this->assertSame(1, $request->getElectionsRoundsCount());

        $request->setElectionPresidentialSecondRound(true);
        $this->assertSame(2, $request->getElectionsRoundsCount());

        $request->setElectionLegislativeFirstRound(true);
        $this->assertSame(3, $request->getElectionsRoundsCount());

        $request->setElectionLegislativeSecondRound(true);
        $this->assertSame(4, $request->getElectionsRoundsCount());
    }
}
