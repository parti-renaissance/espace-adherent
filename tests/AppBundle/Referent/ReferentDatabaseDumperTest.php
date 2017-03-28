<?php

namespace Tests\AppBundle\Referent;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use AppBundle\Referent\ReferentDatabaseDumper;
use Tests\AppBundle\SqliteWebTestCase;

class ReferentDatabaseDumperTest extends SqliteWebTestCase
{
    /**
     * @var ReferentDatabaseDumper
     */
    private $dumper;

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^The "foobar" export type is unsupported/
     */
    public function testDumpWithUnsupportedFileType()
    {
        $this->dumper->dump(LoadAdherentData::ADHERENT_8_UUID, 'foobar');
    }

    /**
     * @expectedException \AppBundle\Exception\ReferentNotFoundException
     */
    public function testDumpWithUnknownReferent()
    {
        $this->dumper->dump(LoadAdherentData::ADHERENT_1_UUID, 'all');
    }

    public function testDumpPhpFileType()
    {
        $path = $this->getDatabaseDumpedFilePath('serialized');

        $this->assertFileNotExists($path);

        $this->dumper->dump('referent@en-marche-dev.fr', 'serialized');

        $this->assertFileExists($path);
        $this->assertGreaterThan(0, filesize($path));
        $this->assertInternalType('array', $data = unserialize(file_get_contents($path)));
        $this->assertNotEmpty($data);
    }

    /**
     * @dataProvider provideJsonFileType
     */
    public function testDumpJsonFileType(string $type)
    {
        $path = $this->getDatabaseDumpedFilePath($type);

        $this->assertFileNotExists($path);

        $this->dumper->dump(LoadAdherentData::ADHERENT_8_UUID, $type);

        $this->assertFileExists($path);
        $this->assertGreaterThan(0, filesize($path));
        $this->assertJson($json = file_get_contents($path));
        $this->assertInternalType('array', $data = \GuzzleHttp\json_decode($json, true));
        $this->assertNotEmpty($data);
    }

    public function provideJsonFileType()
    {
        return [
            ['all'],
            ['subscribers'],
            ['adherents'],
            ['non_followers'],
            ['followers'],
            ['hosts'],
        ];
    }

    private function getDatabaseDumpedFilePath(string $type)
    {
        return sprintf(
            '%s/data/%s/%s_%s.data',
            $this->getContainer()->getParameter('kernel.root_dir'),
            ReferentDatabaseDumper::STORAGE_SPACE,
            LoadAdherentData::ADHERENT_8_UUID,
            $type
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->cleanup();

        $this->dumper = $this->getContainer()->get('app.referent.database_dumper');

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadEventData::class,
            LoadNewsletterSubscriptionData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->cleanup();

        $this->dumper = null;

        $this->loadFixtures([]);

        parent::tearDown();
    }

    private function cleanup(): void
    {
        $container = $this->getContainer();
        $container->get('app.storage')->deleteDir(ReferentDatabaseDumper::STORAGE_SPACE);
    }
}
