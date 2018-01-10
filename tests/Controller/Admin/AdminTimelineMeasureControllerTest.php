<?php

namespace Tests\AppBundle\Controller\Admin;

use AppBundle\Algolia\ManualIndexer;
use AppBundle\DataFixtures\ORM\LoadAdminData;
use AppBundle\DataFixtures\ORM\LoadTimelineData;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Theme;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class AdminTimelineMeasureControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    private $measureRepository;
    private $themeRepository;

    public function testIndexedThemesAfterMeasureUpdate()
    {
        /* @var $measure Measure */
        $measure = $this->measureRepository->findOneByTitle(LoadTimelineData::MEASURES['TM001']['title']['fr']);
        $currentTheme = $this->themeRepository->findOneByTitle(LoadTimelineData::THEMES['TT001']['title']['fr']);
        $newTheme = $this->themeRepository->findOneByTitle(LoadTimelineData::THEMES['TT003']['title']['fr']);

        $this->assertTrue($measure->getThemes()->contains($currentTheme));
        $this->assertFalse($measure->getThemes()->contains($newTheme));
        $this->assertTrue($measure->getThemesToIndex()->contains($currentTheme));
        $this->assertFalse($measure->getThemesToIndex()->contains($newTheme));

        $crawler = $this->client->request(Request::METHOD_GET, '/admin/login');

        // connect as admin
        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_admin_email' => 'admin@en-marche-dev.fr',
            '_admin_password' => 'admin',
        ]));

        $editUrl = sprintf('/admin/app/timeline-measure/%s/edit', $measure->getId());
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );

        $this->client->submit($crawler->selectButton('Mettre à jour')->form([
            $formName.'[themes]' => [$newTheme->getId()],
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $indexedEntities = $this->client->getContainer()->get(ManualIndexer::class)->getCreations();

        $this->assertTrue(is_array($indexedEntities));
        $this->assertCount(3, $indexedEntities);

        /* @var $measure Measure */
        $measure = $indexedEntities[0];
        $this->assertInstanceOf(Measure::class, $measure);
        $this->assertSame(LoadTimelineData::MEASURES['TM001']['title']['fr'], $measure->getTitle());

        /* @var $oldTheme Theme */
        $oldTheme = $indexedEntities[1];
        $this->assertInstanceOf(Theme::class, $oldTheme);
        $this->assertSame(LoadTimelineData::THEMES['TT001']['title']['fr'], $oldTheme->getTitle());

        /* @var $currentTheme Theme */
        $currentTheme = $indexedEntities[2];
        $this->assertInstanceOf(Theme::class, $currentTheme);
        $this->assertSame(LoadTimelineData::THEMES['TT003']['title']['fr'], $currentTheme->getTitle());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdminData::class,
            LoadTimelineData::class,
        ]);

        $this->get('doctrine.orm.entity_manager')->getFilters()->disable('oneLocale');

        $this->measureRepository = $this->getRepository(Measure::class);
        $this->themeRepository = $this->getRepository(Theme::class);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->measureRepository = null;
        $this->themeRepository = null;

        parent::tearDown();
    }
}
