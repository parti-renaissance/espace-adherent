<?php

namespace Tests\AppBundle\Controller\Admin;

use AppBundle\Algolia\ManualIndexer;
use AppBundle\DataFixtures\ORM\LoadAdminData;
use AppBundle\DataFixtures\ORM\LoadTimelineData;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Profile;
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

    private $manualIndexer;
    private $measureRepository;
    private $themeRepository;
    private $profileRepository;

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

        $this->assertCount(2, $indexedEntities);
        $this->assertArrayHasKey('Measure_test', $indexedEntities);
        $this->assertArrayHasKey('Theme_test', $indexedEntities);
        $this->assertCount(1, $indexedEntities['Measure_test']);
        $this->assertCount(2, $indexedEntities['Theme_test']);

        $measurePayload = $indexedEntities['Measure_test'][0];
        $oldThemePayload = $indexedEntities['Theme_test'][0];
        $newThemePayload = $indexedEntities['Theme_test'][1];

        $this->assertArraySubset([
            'id' => $measure->getId(),
            'link' => '',
            'status' => 'IN_PROGRESS',
            'major' => false,
            'profileIds' => [
                $this->profileRepository->findOneByTitle(LoadTimelineData::PROFILES['TP002']['title']['fr'])->getId(),
                $this->profileRepository->findOneByTitle(LoadTimelineData::PROFILES['TP003']['title']['fr'])->getId(),
            ],
            'titles' => [
                'fr' => LoadTimelineData::MEASURES['TM001']['title']['fr'],
                'en' => LoadTimelineData::MEASURES['TM001']['title']['en'],
            ],
        ], $measurePayload);

        $this->assertArrayHasKey('objectID', $measurePayload);
        $this->assertNotEmpty($measurePayload);

        $this->assertArrayHasKey('formattedUpdatedAt', $measurePayload);
        $this->assertNotEmpty($measurePayload);

        $this->assertArraySubset([
            'id' => $currentTheme->getId(),
            'featured' => true,
            'image' => null,
            'measureIds' => $this->getMeasureIdsByTitles([
                LoadTimelineData::MEASURES['TM002']['title']['fr'],
                LoadTimelineData::MEASURES['TM003']['title']['fr'],
                LoadTimelineData::MEASURES['TM004']['title']['fr'],
                LoadTimelineData::MEASURES['TM005']['title']['fr'],
                LoadTimelineData::MEASURES['TM006']['title']['fr'],
                LoadTimelineData::MEASURES['TM007']['title']['fr'],
                LoadTimelineData::MEASURES['TM008']['title']['fr'],
                LoadTimelineData::MEASURES['TM012']['title']['fr'],
            ]),
            'profileIds' => [
                $this->profileRepository->findOneByTitle(LoadTimelineData::PROFILES['TP002']['title']['fr'])->getId(),
                $this->profileRepository->findOneByTitle(LoadTimelineData::PROFILES['TP003']['title']['fr'])->getId(),
                $this->profileRepository->findOneByTitle(LoadTimelineData::PROFILES['TP004']['title']['fr'])->getId(),
                $this->profileRepository->findOneByTitle(LoadTimelineData::PROFILES['TP001']['title']['fr'])->getId(),
                $this->profileRepository->findOneByTitle(LoadTimelineData::PROFILES['TP005']['title']['fr'])->getId(),
            ],
            'titles' => [
                'fr' => LoadTimelineData::THEMES['TT001']['title']['fr'],
                'en' => LoadTimelineData::THEMES['TT001']['title']['en'],
            ],
            'slugs' => [
                'fr' => LoadTimelineData::THEMES['TT001']['slug']['fr'],
                'en' => LoadTimelineData::THEMES['TT001']['slug']['en'],
            ],
            'descriptions' => [
                'fr' => LoadTimelineData::THEMES['TT001']['description']['fr'],
                'en' => LoadTimelineData::THEMES['TT001']['description']['en'],
            ],
        ], $oldThemePayload);

        $this->assertArrayHasKey('objectID', $oldThemePayload);
        $this->assertNotEmpty($oldThemePayload);


        return;
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

        $this->manualIndexer = $this->client->getContainer()->get(ManualIndexer::class);
        $this->measureRepository = $this->getRepository(Measure::class);
        $this->themeRepository = $this->getRepository(Theme::class);
        $this->profileRepository = $this->getRepository(Profile::class);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->manualIndexer = null;
        $this->measureRepository = null;
        $this->themeRepository = null;
        $this->profileRepository = null;

        parent::tearDown();
    }

    private function getMeasureIdsByTitles(array $measureTitles): array
    {
        $repository = $this->measureRepository;

        return array_map(function(string $measureTitle) use ($repository) {
            return $repository->findOneByTitle($measureTitle)->getId(),
        }, $measureTitle);
    }
}
