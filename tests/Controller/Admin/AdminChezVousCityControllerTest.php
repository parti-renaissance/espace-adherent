<?php

namespace Tests\AppBundle\Controller\Admin;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Repository\ChezVous\CityRepository;
use Doctrine\ORM\EntityRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\Test\Algolia\DummyIndexer;

/**
 * @group functional
 * @group admin
 */
class AdminChezVousCityControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var EntityRepository
     */
    private $cityRepository;

    public function testUnindexedCityAfterRemoval()
    {
        /** @var City $city */
        $city = $this->cityRepository->findOneByInseeCode('06088');

        $this->authenticateAsAdmin($this->client);

        $deleteUrl = sprintf('/admin/app/chezvous-city/%s/delete', $city->getId());
        $crawler = $this->client->request(Request::METHOD_GET, $deleteUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Oui, supprimer')->form());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $entitiesToIndex = $this->getIndexer()->getEntitiesToIndex();
        $entitiesToUnIndex = $this->getIndexer()->getEntitiesToUnIndex();

        $this->assertCount(1, $entitiesToUnIndex);
        $this->assertArrayHasKey('City_test', $entitiesToUnIndex);
        $this->assertCount(1, $entitiesToUnIndex['City_test']);
        $this->assertEmpty($entitiesToIndex);
    }

    public function testIndexedCityAfterUpdate()
    {
        /* @var City $city */
        $city = $this->cityRepository->findOneByInseeCode('06088');

        $this->authenticateAsAdmin($this->client);

        $editUrl = sprintf('/admin/app/chezvous-city/%s/edit', $city->getId());
        $crawler = $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );

        $this->client->submit($crawler->selectButton('Mettre à jour')->form([
            $formName.'[name]' => 'Nissa',
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $indexedEntities = $this->getIndexer()->getEntitiesToIndex();

        $this->assertCount(1, $indexedEntities);
        $this->assertArrayHasKey('City_test', $indexedEntities);
        $this->assertCount(1, $indexedEntities['City_test']);

        $cityPayload = $indexedEntities['City_test'][0];

        $this->assertArraySubset([
            'name' => 'Nissa',
            'postalCodes' => [
                '06000',
                '06100',
                '06200',
                '06300',
            ],
            'inseeCode' => '06088',
            'slug' => '06088-nice',
            'department' => [
                'name' => 'Alpes-Maritimes',
                'code' => '06',
                'region' => [
                    'name' => 'Provence-Alpes-Côte d\'Azur',
                    'code' => '93',
                ],
            ],
            'measures' => [
                [
                    'type' => [
                        'code' => 'quartier_reconquete_republicaine',
                        'label' => "Création d'un quartier de reconquête républicaine",
                        'sourceLink' => 'https://www.interieur.gouv.fr/Espace-presse/Dossiers-de-presse/Un-an-de-la-police-de-securite-du-quotidien',
                        'sourceLabel' => 'interieur.gouv.fr',
                        'updatedAt' => '2019/09/05',
                        'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=securite',
                        'eligibilityLink' => null,
                        'citizenProjectsLink' => 'https://en-marche.fr/projets-citoyens',
                        'ideasWorkshopLink' => 'https://en-marche.fr/atelier-des-idees/proposer',
                    ],
                    'payload' => null,
                ],
                [
                    'type' => [
                        'code' => 'baisse_nombre_chomeurs',
                        'label' => 'Baisse du nombre de chômeurs',
                        'sourceLink' => 'https://statistiques.pole-emploi.org/stmt/trsl?fa=M&lb=0',
                        'sourceLabel' => 'pole-emploi.org',
                        'updatedAt' => '2019/09/05',
                        'oldolfLink' => 'https://transformer.en-marche.fr/fr/results?theme=fiscalite,travail,entreprises,industrie,apprentissage,dialogue-social',
                        'eligibilityLink' => null,
                        'citizenProjectsLink' => 'https://en-marche.fr/projets-citoyens',
                        'ideasWorkshopLink' => 'https://en-marche.fr/atelier-des-idees/proposer',
                    ],
                    'payload' => [
                        'baisse_ville' => 300,
                        'baisse_departement' => 4000,
                    ],
                ],
            ],
            'markers' => [
                [
                    'type' => 'maison_service_accueil_public',
                    'coordinates' => [
                        43.701,
                        7.254,
                    ],
                ],
                [
                    'type' => 'maison_service_accueil_public',
                    'coordinates' => [
                        43.676,
                        7.207,
                    ],
                ],
            ],
            '_geoloc' => [
                'lat' => 43.7,
                'lng' => 7.25,
            ],
        ], $cityPayload);
        $this->assertArrayHasKey('objectID', $cityPayload);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->get('doctrine.orm.entity_manager')->getFilters()->disable('oneLocale');

        $this->cityRepository = $this->get(CityRepository::class);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->cityRepository = null;

        parent::tearDown();
    }

    private function getIndexer(): DummyIndexer
    {
        return $this->client->getContainer()->get('algolia.indexer');
    }
}
