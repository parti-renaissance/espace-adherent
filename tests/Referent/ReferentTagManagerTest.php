<?php

namespace Tests\AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
use AppBundle\Referent\ReferentTagManager;
use AppBundle\Repository\ReferentTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ReferentTagManagerTest extends TestCase
{
    /**
     * @var ReferentTagManager
     */
    private $referentTagManager;

    protected function setUp()
    {
        $this->referentTagManager = new ReferentTagManager($this->createMock(ReferentTagRepository::class));
    }

    protected function tearDown()
    {
        $this->referentTagManager = null;
    }

    /**
     * @dataProvider providesTestIsUpdateNeeded
     */
    public function testIsUpdateNeeded(bool $isUpdateNeeded, string $postalCode, array $referentCodes): void
    {
        $adherent = $this->createAdherent($postalCode, $referentCodes);

        $this->assertSame($isUpdateNeeded, $this->referentTagManager->isUpdateNeeded($adherent));
    }

    public function providesTestIsUpdateNeeded(): array
    {
        return [
            [false, '73100', ['73']],
            [true, '75001', ['75']],
            [false, '75001', ['75', '75001']],
            [true, '75001', ['75', '75001', 'France']],
        ];
    }

    /**
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAdherent($postalCode, array $referentCodes): Adherent
    {
        $adherent = $this->createMock(Adherent::class);

        $tags = new ArrayCollection();
        foreach ($referentCodes as $code) {
            $tags->add(new ReferentTag(null, $code));
        }

        $adherent->method('getCountry')->willReturn('FR');
        $adherent->method('getReferentTags')->willReturn($tags);
        $adherent->method('getPostalCode')->willReturn($postalCode);

        return $adherent;
    }
}
