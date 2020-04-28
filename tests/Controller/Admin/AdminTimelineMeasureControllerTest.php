<?php

namespace Tests\AppBundle\Controller\Admin;

use Algolia\AlgoliaSearchBundle\Indexer\Indexer;
use AppBundle\DataFixtures\ORM\LoadTimelineData;
use AppBundle\Entity\Timeline\Manifesto;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\Theme;
use AppBundle\Repository\Timeline\ManifestoRepository;
use AppBundle\Repository\Timeline\MeasureRepository;
use AppBundle\Repository\Timeline\ProfileRepository;
use AppBundle\Repository\Timeline\ThemeRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\Test\Algolia\DummyIndexer;

/**
 * @group functional
 * @group admin
 */
class AdminTimelineMeasureControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var MeasureRepository
     */
    private $measureRepository;

    /**
     * @var ThemeRepository
     */
    private $themeRepository;

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var ManifestoRepository
     */
    private $manifestoRepository;

    public function testUnindexedMeasureAfterMeasureRemoval()
    {
        /* @var $measure Measure */
        $measure = $this->measureRepository->findOneByTitle(LoadTimelineData::MEASURES['TM001']['title']['fr']);

        $this->authenticateAsAdmin($this->client);

        $deleteUrl = sprintf('/admin/app/timeline-measure/%s/delete', $measure->getId());
        $crawler = $this->client->request(Request::METHOD_GET, $deleteUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Oui, supprimer')->form());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $entitiesToIndex = $this->getIndexer()->getEntitiesToIndex();
        $entitiesToUnIndex = $this->getIndexer()->getEntitiesToUnIndex();

        $this->assertCount(1, $entitiesToUnIndex);
        $this->assertArrayHasKey('Measure_test', $entitiesToUnIndex);
        $this->assertCount(1, $entitiesToUnIndex['Measure_test']);

        $this->assertCount(1, $entitiesToIndex);
        $this->assertArrayHasKey('Theme_test', $entitiesToIndex);
        $this->assertCount(1, $entitiesToIndex['Theme_test']);
        $this->assertArraySubset([
            'titles' => [
                'fr' => LoadTimelineData::THEMES['TT001']['title']['fr'],
            ],
        ], $entitiesToIndex['Theme_test'][0]);
    }

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

        $this->authenticateAsAdmin($this->client);

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

        $indexedEntities = $this->getIndexer()->getEntitiesToIndex();

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
            'major' => true,
            'profileIds' => $this->getProfileIdsByTitles([
                LoadTimelineData::PROFILES['TP002']['title']['fr'],
                LoadTimelineData::PROFILES['TP003']['title']['fr'],
            ]),
            'manifestoId' => $this->getManifestoIdByTitle(LoadTimelineData::MANIFESTOS['TMA001']['title']['fr']),
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
            'profileIds' => $this->getProfileIdsByTitles([
                LoadTimelineData::PROFILES['TP002']['title']['fr'],
                LoadTimelineData::PROFILES['TP003']['title']['fr'],
                LoadTimelineData::PROFILES['TP004']['title']['fr'],
                LoadTimelineData::PROFILES['TP001']['title']['fr'],
                LoadTimelineData::PROFILES['TP005']['title']['fr'],
            ]),
            'manifestoIds' => [
                $this->getManifestoIdByTitle(LoadTimelineData::MANIFESTOS['TMA001']['title']['fr']),
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
        $this->assertNotEmpty($oldThemePayload['objectID']);

        $this->assertArraySubset([
            'id' => $newTheme->getId(),
            'featured' => true,
            'image' => null,
            'measureIds' => $this->getMeasureIdsByTitles([
                LoadTimelineData::MEASURES['TM001']['title']['fr'],
                LoadTimelineData::MEASURES['TM005']['title']['fr'],
                LoadTimelineData::MEASURES['TM014']['title']['fr'],
                LoadTimelineData::MEASURES['TM017']['title']['fr'],
            ]),
            'profileIds' => $this->getProfileIdsByTitles([
                LoadTimelineData::PROFILES['TP002']['title']['fr'],
                LoadTimelineData::PROFILES['TP003']['title']['fr'],
                LoadTimelineData::PROFILES['TP005']['title']['fr'],
                LoadTimelineData::PROFILES['TP001']['title']['fr'],
            ]),
            'manifestoIds' => [
                $this->getManifestoIdByTitle(LoadTimelineData::MANIFESTOS['TMA001']['title']['fr']),
                $this->getManifestoIdByTitle(LoadTimelineData::MANIFESTOS['TMA003']['title']['fr']),
            ],
            'titles' => [
                'fr' => LoadTimelineData::THEMES['TT003']['title']['fr'],
                'en' => LoadTimelineData::THEMES['TT003']['title']['en'],
            ],
            'slugs' => [
                'fr' => LoadTimelineData::THEMES['TT003']['slug']['fr'],
                'en' => LoadTimelineData::THEMES['TT003']['slug']['en'],
            ],
            'descriptions' => [
                'fr' => LoadTimelineData::THEMES['TT003']['description']['fr'],
                'en' => LoadTimelineData::THEMES['TT003']['description']['en'],
            ],
        ], $newThemePayload);

        $this->assertArrayHasKey('objectID', $newThemePayload);
        $this->assertNotEmpty($newThemePayload['objectID']);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->get('doctrine.orm.entity_manager')->getFilters()->disable('oneLocale');

        $this->measureRepository = $this->getRepository(Measure::class);
        $this->themeRepository = $this->getRepository(Theme::class);
        $this->profileRepository = $this->getRepository(Profile::class);
        $this->manifestoRepository = $this->getRepository(Manifesto::class);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->measureRepository = null;
        $this->themeRepository = null;
        $this->profileRepository = null;
        $this->manifestoRepository = null;

        parent::tearDown();
    }

    private function getMeasureIdsByTitles(array $measureTitles): array
    {
        return array_map(function (string $measureTitle) {
            return $this->measureRepository->findOneByTitle($measureTitle)->getId();
        }, $measureTitles);
    }

    private function getProfileIdsByTitles(array $profileTitles): array
    {
        return array_map(function (string $profileTitle) {
            return $this->profileRepository->findOneByTitle($profileTitle)->getId();
        }, $profileTitles);
    }

    private function getManifestoIdByTitle(string $manifestoTitle): int
    {
        return $this->manifestoRepository->findOneByTitle($manifestoTitle)->getId();
    }

    private function getIndexer(): DummyIndexer
    {
        return $this->client->getContainer()->get(Indexer::class);
    }
}
