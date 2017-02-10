<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Committee\CommitteeUtils;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use Ramsey\Uuid\Uuid;
use Tests\AppBundle\MysqlWebTestCase;
use Tests\AppBundle\TestHelperTrait;

class CommitteeUtilsTest extends MysqlWebTestCase
{
    use TestHelperTrait;

    public function testGetUuidsFromJson()
    {
        $this->assertSame([], CommitteeUtils::getUuidsFromJson(''));
        $this->assertSame([], CommitteeUtils::getUuidsFromJson('not valid JSON syntax'));
        $this->assertSame([], CommitteeUtils::getUuidsFromJson('[]'));
        $this->assertSame([], CommitteeUtils::getUuidsFromJson('{}'));
        $this->assertSame([], CommitteeUtils::getUuidsFromJson('"dfbb56f5-e7df-4fdc-9777-e8237cef4872"'));
        $this->assertSame([], CommitteeUtils::getUuidsFromJson('["e7df-4fdc-9777-e8237cef4872"]'));

        $stringUuids = [
            'dfbb56f5-e7df-4fdc-9777-e8237cef4872',
            '1501e14a-56c9-4876-b5aa-11fa046bc838',
            '53d4ab7f-dcec-4083-9486-feac8d791325',
        ];

        $objectUuids = array_map(function ($item) {
            return Uuid::fromString($item);
        }, $stringUuids);

        $this->assertEquals($objectUuids, CommitteeUtils::getUuidsFromJson(json_encode($stringUuids)));
    }

    public function testRemoveUnknownAdherents()
    {
        $adherents = $this->getAdherents();

        $this->assertSame([], CommitteeUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID],
            []
        ));
        $this->assertSame([], CommitteeUtils::removeUnknownAdherents(
            [Uuid::fromString(LoadAdherentData::ADHERENT_1_UUID)],
            []
        ));
        $this->assertSame([], CommitteeUtils::removeUnknownAdherents(
            [],
            $adherents
        ));
        $this->assertSame([$adherents[0]], CommitteeUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID],
            $adherents
        ));
        $this->assertSame($adherents, CommitteeUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID, LoadAdherentData::ADHERENT_2_UUID],
            $adherents
        ));
        $this->assertSame($adherents, CommitteeUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID, LoadAdherentData::ADHERENT_2_UUID, LoadAdherentData::ADHERENT_3_UUID],
            $adherents
        ));
    }

    public function testRemoveUnknownAdherentsBadCallCollection()
    {
        $this->expectException(\BadMethodCallException::class);

        CommitteeUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID],
            ['this is not a collection of adherents']
        );
    }

    public function testRemoveUnknownAdherentsBadCallIterable()
    {
        $this->expectException(\BadMethodCallException::class);

        CommitteeUtils::removeUnknownAdherents(
            [LoadAdherentData::ADHERENT_1_UUID],
            'this is not an iterable value'
        );
    }

    public function testGetUuidsFromAdherents()
    {
        $adherents = $this->getAdherents();
        $uuids = CommitteeUtils::getUuidsFromAdherents($adherents);

        $this->assertCount(count($adherents), $uuids);

        foreach ($uuids as $uuid) {
            $this->assertInternalType('string', $uuid);
            $this->assertTrue(Uuid::isValid($uuid));
        }
    }

    public function testGetUuidsFromAdherentsBadCallCollection()
    {
        $this->expectException(\BadMethodCallException::class);

        CommitteeUtils::getUuidsFromAdherents(['this is not a collection of adherents']);
    }

    public function testGetUuidsFromAdherentsBadCallIterable()
    {
        $this->expectException(\BadMethodCallException::class);

        CommitteeUtils::getUuidsFromAdherents('this is not an iterable value');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->adherents = null;

        parent::tearDown();
    }
}
