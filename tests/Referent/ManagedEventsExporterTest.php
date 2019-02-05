<?php

namespace Tests\AppBundle\Referent;

use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Referent\ManagedEventsExporter;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 */
class ManagedEventsExporterTest extends WebTestCase
{
    /**
     * @var ManagedEventsExporter
     */
    private $exporter;

    use ControllerTestTrait;

    public function testExportAsJson()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_1_UUID);

        $expectedBeginAt = $event->getBeginAt()->format('d\\\/m\\\/Y H:i');
        $expectedDate = $event->getBeginAt()->format('Y-m-d');

        $this->assertSame(
            '[{"id":2,"name":{"label":"R\u00e9union de r\u00e9flexion parisienne","url":"'.$this->hosts['scheme'].':\/\/'.$this->hosts['app'].'\/evenements\/'.$expectedDate.'-reunion-de-reflexion-parisienne"},"beginAt":"'.$expectedBeginAt.'","category":"Atelier du programme","postalCode":"75008","organizer":"Jacques P.","participants":1,"type":"Comit\u00e9"}]',
            $this->exporter->exportAsJson([$event])
        );
    }

    public function testExportAsJsonWithNoOrganizer()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_14_UUID);

        $expectedBeginAt = $event->getBeginAt()->format('d\\\/m\\\/Y H:i');
        $expectedDate = $event->getBeginAt()->format('Y-m-d');

        $this->assertSame(
            '[{"id":15,"name":{"label":"Meeting #11 de Brooklyn","url":"'.$this->hosts['scheme'].':\/\/'.$this->hosts['app'].'\/evenements\/'.$expectedDate.'-meeting-11-de-brooklyn"},"beginAt":"'.$expectedBeginAt.'","category":"Marche","postalCode":"10019","organizer":"un ancien adh\u00e9rent","participants":0,"type":"Comit\u00e9"}]',
            $this->exporter->exportAsJson([$event])
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->exporter = $this->container->get(ManagedEventsExporter::class);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->exporter = null;

        parent::tearDown();
    }
}
