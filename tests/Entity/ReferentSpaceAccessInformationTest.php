<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentSpaceAccessInformation;
use PHPUnit\Framework\TestCase;

class ReferentSpaceAccessInformationTest extends TestCase
{
    public function testUpdatePageAccessInformation(): void
    {
        $firstDate = '2019-04-09 09:09:09';
        $secondDate = '2019-04-09 11:00:00';
        $thirdDate = '2019-04-10 10:10:10';
        $referentSpaceAccessInformation = $this->createReferentSpaceAccessInformation($firstDate);

        $referentSpaceAccessInformation->update($secondDate);

        $this->assertSame($firstDate, $referentSpaceAccessInformation->getPreviousDate()->format('Y-m-d H:i:s'));
        $this->assertSame($secondDate, $referentSpaceAccessInformation->getLastDate()->format('Y-m-d H:i:s'));

        $referentSpaceAccessInformation->update($thirdDate);

        $this->assertSame($secondDate, $referentSpaceAccessInformation->getPreviousDate()->format('Y-m-d H:i:s'));
        $this->assertSame($thirdDate, $referentSpaceAccessInformation->getLastDate()->format('Y-m-d H:i:s'));
    }

    private function createReferentSpaceAccessInformation(string $datetime): ReferentSpaceAccessInformation
    {
        $adherent = $this->createMock(Adherent::class);

        return new ReferentSpaceAccessInformation($adherent, new \DateTimeImmutable($datetime), new \DateTimeImmutable($datetime));
    }
}
