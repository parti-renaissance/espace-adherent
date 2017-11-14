<?php

namespace Tests\AppBundle\Referent;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Referent\ManagedEventsExporter;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class ManagedEventsExporterTest extends SqliteWebTestCase
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

        $this->assertSame(
            '[{"id":1,"name":{"label":"R\u00e9union de r\u00e9flexion parisienne","url":"\/\/'.$this->hosts['app'].'\/evenements\/2017-11-17-reunion-de-reflexion-parisienne"},"beginAt":"'.$expectedBeginAt.'","category":"Atelier du programme","postalCode":"75008","organizer":"Jacques P.","participants":1}]',
            $this->exporter->exportAsJson([$event])
        );
    }

    public function testExportAsJsonWithNoOrganizer()
    {
        $event = $this->getEventRepository()->findOneByUuid(LoadEventData::EVENT_14_UUID);

        $expectedBeginAt = $event->getBeginAt()->format('d\\\/m\\\/Y H:i');

        $this->assertSame(
            '[{"id":14,"name":{"label":"Meeting #11 de Brooklyn","url":"\/\/'.$this->hosts['app'].'\/evenements\/2017-11-14-meeting-11-de-brooklyn"},"beginAt":"'.$expectedBeginAt.'","category":"Marche","postalCode":"10019","organizer":"un ancien adh\u00e9rent","participants":0}]',
            $this->exporter->exportAsJson([$event])
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
        ]);

        $this->exporter = $this->container->get('app.referent.managed_events.exporter');
    }

    protected function tearDown()
    {
        $this->kill();

        $this->exporter = null;

        parent::tearDown();
    }
}
