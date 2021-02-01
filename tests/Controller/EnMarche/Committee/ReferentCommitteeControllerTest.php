<?php

namespace Tests\App\Controller\EnMarche\Committee;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\Entity\Committee;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ReferentCommitteeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testListElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/comites');

        $this->assertCount(4, $crawler->filter('tbody tr.committee__item'));

        $this->assertStringContainsString('En Marche - Suisse', $crawler->filter('tbody tr.committee__item')->eq(0)->text());
        $this->assertStringContainsString('0 F / 0 H', $crawler->filter('tbody tr.committee__item')->eq(0)->text());

        $this->assertStringContainsString('En Marche - Comité de Rouen', $crawler->filter('tbody tr.committee__item')->eq(1)->text());
        $this->assertStringContainsString('0 F / 2 H', $crawler->filter('tbody tr.committee__item')->eq(1)->text());

        $this->assertStringContainsString('En Marche Dammarie-les-Lys', $crawler->filter('tbody tr.committee__item')->eq(2)->text());

        $this->assertStringContainsString('Antenne En Marche de Fontainebleau', $crawler->filter('tbody tr.committee__item')->eq(3)->text());
    }

    public function testReferentCanAccessCommitteeCreationPage()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $crawler = $this->client->request('GET', '/parametres/mes-activites#committees');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $creationLink = $crawler->selectLink('Demander la création d\'un comité'));

        $this->client->click($creationLink->link());

        $this->assertEquals('http://'.$this->hosts['app'].'/espace-referent/comites/creer', $this->client->getRequest()->getUri());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testReferentCanCreateCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'referent-child@en-marche-dev.fr');
        $crawler = $this->client->request('GET', '/espace-referent/comites/creer');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        // Submit the committee form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Créer le comité')->form([
            'committee' => [
                'name' => 'F',
                'description' => 'F',
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
            ],
        ]));

        $this->assertSame(7, $crawler->filter('#create-committee-form .form__errors > li')->count());
        $this->assertSame("L'adresse saisie ne fait pas partie de la zone géographique que vous gérez", $crawler->filter('#committee_address_errors > li')->eq(0)->text());
        $this->assertSame("Cette valeur n'est pas un code postal français valide.", $crawler->filter('#committee_address_errors > li')->eq(1)->text());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $crawler->filter('#committee_address_errors > li')->eq(2)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#committee_address_address_errors > li')->text());
        $this->assertSame('Vous devez saisir au moins 2 caractères.', $crawler->filter('#committee_name_errors > li.form__error')->text());
        $this->assertSame('Votre texte de description est trop court. Il doit compter 5 caractères minimum.', $crawler->filter('#committee_description_errors > li')->text());
        $this->assertSame('Vous devez séléctionner au moins un Animateur provisoire parmi vos adhérents', $crawler->filter('#field-provisional-supervisor-female li')->text());

        // Submit the committee form with valid data to create committee
        $crawler = $this->client->submit($crawler->selectButton('Créer le comité')->form([
            'committee[name]' => 'Nouveau comité Dammarie-les-Lys',
            'committee[description]' => 'Comité français En Marche !',
            'committee[address][address]' => '826 avenue du lys',
            'committee[address][postalCode]' => '77190',
            'committee[address][city]' => '77190-77152',
            'committee[address][cityName]' => 'dammarie-les-lys',
            'committee[address][country]' => 'FR',
            'committee[provisionalSupervisorMale]' => $this->getAdherent(LoadAdherentData::ADHERENT_6_UUID)->getId(),
        ]));

        $this->assertSame(0, $crawler->filter('#create-committee-form .form__errors > li')->count());

        $this->assertInstanceOf(Committee::class, $committee = $this->getCommitteeRepository()->findMostRecentCommittee());
        $this->assertSame('Nouveau comité Dammarie-les-Lys', $committee->getName());
        $this->assertTrue($committee->isWaitingForApproval());

        $this->assertClientIsRedirectedTo('/espace-referent/comites/demandes', $this->client);

        $crawler = $this->client->followRedirect();

        $this->seeFlashMessage($crawler, 'Votre comité a été créé avec succès.
Il ne manque plus que la validation d\'un coordinateur régional pour qu\'il soit pleinement opérationnel.');
    }

    public function testReferentCanSeeCommitteeRequests()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $crawler = $this->client->request('GET', '/espace-referent/comites/demandes');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $committees = $crawler->filter('table.datagrid__table-manager tbody tr'));
        $fields = $committees->first()->filter('td');
        $this->assertSame('03/01/2021', $fields->eq(0)->text());
        $this->assertStringContainsString('Une nouvelle demande', $fields->eq(1)->text());
        $this->assertSame('824 Avenue du Lys, 77190 Dammarie-les-Lys, FR', $fields->eq(2)->text());
        $this->assertSame('Referent Referent', $fields->eq(3)->text());
        $this->assertStringContainsString('En attente', $fields->eq(4)->text());
    }

    public function testAccessCommitteeRequestsList()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/comites');

        $this->assertStringContainsString('Vous avez 1 demande de création de comité en attente de traitement.', $crawler->filter('.committee__info')->text());

        $this->client->click($crawler->selectLink('Voir')->link());

        $this->assertEquals('http://'.$this->hosts['app'].'/espace-referent/comites/demandes', $this->client->getRequest()->getUri());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testPreAcceptCommitteeRequest()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/comites/demandes');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->checkCommitteeRequestStatus($crawler, 'En attente');

        $crawler = $this->client->request(
            Request::METHOD_GET,
            '/espace-referent/comites/une-nouvelle-demande/pre-approuver'
        );

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $adherente = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_4_UUID);
        $this->client->submit($crawler->selectButton('Pré-approuver')->form([
            'committee' => [
                'provisionalSupervisorFemale' => $adherente->getId(),
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Le comité a été pré-approuvé.');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->checkCommitteeRequestStatus($crawler, 'Pré-approuvé');
    }

    public function testPreRefuseCommitteeRequest()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/comites/demandes');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->checkCommitteeRequestStatus($crawler, 'En attente');

        $this->client->request(
            Request::METHOD_GET,
           '/espace-referent/comites/une-nouvelle-demande/pre-refuser'
        );

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-referent/comites/demandes', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->checkCommitteeRequestStatus($crawler, 'Pré-refusé');
    }

    /**
     * @dataProvider provideAdherents
     */
    public function testCanMakePreActions(string $email, string $committeeUuid, string $action, int $statusCode)
    {
        $committee = $this->getCommitteeRepository()->findOneByUuid($committeeUuid);
        $this->authenticateAsAdherent($this->client, $email);

        $this->client->request(Request::METHOD_GET,
            \sprintf('/espace-referent/comites/%s/%s', $committee->getSlug(), $action)
        );

        $this->assertResponseStatusCode($statusCode, $this->client->getResponse());
    }

    public function testReferentCanSeeCommitteeElectionList()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/comites/designations');

        $this->assertCount(2, $table = $crawler->filter('tbody tr'));

        $this->assertStringContainsString('En Marche - Comité de Rouen', $table->eq(0)->text());
        $this->assertStringContainsString('Programmée', $table->eq(0)->text());
        $this->assertStringContainsString('2 candidatures', $table->eq(0)->text());
        $this->assertStringContainsString('0 pré-candidature', $table->eq(0)->text());

        $this->assertStringContainsString('Antenne En Marche de Fontainebleau', $table->eq(1)->text());
        $this->assertStringContainsString('Terminée', $table->eq(1)->text());
        $this->assertStringContainsString('Détails', $table->eq(1)->text());

        $form = $crawler->selectButton('Appliquer')->form();
        $crawler = $this->client->submit($form, ['f' => ['committeeName' => 'Fontainebleau']]);

        $this->assertCount(1, $table = $crawler->filter('tbody tr'));

        $crawler = $this->client->click($crawler->selectLink('Détails')->link());
        $this->assertStringEndsWith('/espace-referent/comites/antenne-en-marche-de-fontainebleau/designations/b81c3585-c802-48f6-9dca-19d1d4e08c44', $crawler->getUri());
    }

    public function testReferentCanSeeCommitteeElectionDetails()
    {
        $this->authenticateAsAdherent($this->client, 'adherent-female-32@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/comites/designations');
        $crawler = $this->client->click($crawler->selectLink('Détails')->link());

        $this->assertStringEndsWith('/espace-referent/comites/en-marche-allemagne-3/designations/13814072-1dd2-11b2-9593-b97d988be702', $crawler->getUri());

        $this->assertStringContainsString('Élection du binôme paritaire d\'Animateurs locaux', $crawler->filter('.datagrid__pre-table')->text());

        $crawler = $this->client->click($crawler->selectLink('Liste d\'émargement')->link());

        $this->assertCount(6, $table = $crawler->filter('tbody tr'));
        $this->assertStringContainsString('Adherent 33 Fa33ke', $crawler->filter('table')->text());

        $crawler = $this->client->click($crawler->selectLink('Résultats Animateurs Locaux')->link());
        $this->assertStringContainsString('Résultats par scrutin : Animateurs Locaux', $crawler->filter('.datagrid__pre-table.b__nudge--bottom-50 h3')->text());

        $tableContent = $crawler->filter('table')->text();
        $this->assertStringContainsString('Très bien', $tableContent);
        $this->assertStringContainsString('Bien', $tableContent);
        $this->assertStringContainsString('Insuffisant', $tableContent);

        $crawler = $this->client->click($crawler->selectLink('Bulletins dépouillés')->link());

        $this->assertCount(5, $table = $crawler->filter('tbody tr'));

        $tableHeader = $crawler->filter('thead')->text();
        $this->assertStringContainsString('Adherent 32 Fa32ke', $tableHeader);
        $this->assertStringContainsString('Adherent 33 Fa33ke', $tableHeader);
        $this->assertStringContainsString('Adherent 34 Fa34ke', $tableHeader);
        $this->assertStringContainsString('Adherent 35 Fa35ke', $tableHeader);
        $this->assertStringContainsString('Adherent 36 Fa36ke', $tableHeader);
        $this->assertStringContainsString('Adherent 37 Fa37ke', $tableHeader);
    }

    public function provideAdherents()
    {
        return [
            ['referent@en-marche-dev.fr', LoadCommitteeData::COMMITTEE_16_UUID, 'pre-approuver', Response::HTTP_OK],
            ['referent@en-marche-dev.fr', LoadCommitteeData::COMMITTEE_16_UUID, 'pre-refuser', Response::HTTP_FOUND],
            ['referent@en-marche-dev.fr', LoadCommitteeData::COMMITTEE_1_UUID, 'pre-approuver', Response::HTTP_FORBIDDEN],
            ['referent@en-marche-dev.fr', LoadCommitteeData::COMMITTEE_1_UUID, 'pre-refuser', Response::HTTP_FORBIDDEN],
            ['coordinateur@en-marche-dev.fr', LoadCommitteeData::COMMITTEE_15_UUID, 'pre-approuver', Response::HTTP_FORBIDDEN],
            ['coordinateur@en-marche-dev.fr', LoadCommitteeData::COMMITTEE_15_UUID, 'pre-refuser', Response::HTTP_FORBIDDEN],
        ];
    }

    private function checkCommitteeRequestStatus($crawler, string $status): void
    {
        $requests = $crawler->filter('table.datagrid__table-manager tbody tr');

        $this->assertCount(1, $requests);

        $fields = $requests->first()->filter('td');

        $this->assertStringContainsString($status, $fields->eq(4)->text());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }
}
