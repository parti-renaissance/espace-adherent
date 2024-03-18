<?php

namespace Tests\App\Controller\EnMarche;

use App\Assessor\Filter\AssessorRequestFilters;
use App\Assessor\Filter\CitiesFilters;
use App\Assessor\Filter\VotePlaceFilters;
use App\Mailer\Message\Assessor\AssessorRequestAssociateMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Helper\PHPUnitHelper;

#[Group('functional')]
#[Group('assessor')]
class AssessorManagerControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    private const ASSESSOR_MANAGER_REQUEST_PATH = '/espace-responsable-assesseur';
    private const ASSESSOR_MANAGER_VOTE_PLACE_PATH = self::ASSESSOR_MANAGER_REQUEST_PATH.'/vote-places';
    private const ASSESSOR_MANAGER_VOTE_PLACE_CITIES_PATH = self::ASSESSOR_MANAGER_REQUEST_PATH.'/communes';
    private const ASSESSOR_MANAGER_EMAIL = 'commissaire.biales@example.fr';
    private const SUBJECT_REQUEST = 'demandes? d\'assesseur';
    private const SUBJECT_VOTE_PLACE = 'bureaux? de vote';
    private const SUBJECT_VOTE_PLACE_CITIES = 'communes? assignées';
    private const SUBJECTS = [
        self::SUBJECT_REQUEST,
        self::SUBJECT_VOTE_PLACE,
        self::SUBJECT_VOTE_PLACE_CITIES,
    ];

    #[DataProvider('providePages')]
    public function testAssessorManagerBackendIsForbiddenAsAnonymous(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    #[DataProvider('providePages')]
    public function testAssessorManagerBackendIsForbiddenAsAdherentNotReferent(string $path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public static function providePages(): \Generator
    {
        yield [self::ASSESSOR_MANAGER_REQUEST_PATH];
        yield [self::ASSESSOR_MANAGER_REQUEST_PATH.'/demande/be61ba07-c8e7-4533-97e1-0ab215cd752c'];
        yield [self::ASSESSOR_MANAGER_VOTE_PLACE_PATH];
    }

    public function testAssessorManagerBackendIsForbiddenOnWrongArea()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_REQUEST_PATH.'/demande/d320b698-10b7-4dd7-a70a-cedb95fceeda');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_REQUEST_PATH.'/demande/64b8b8ca-0708-4fcc-a3ce-844ff2e3852d');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function testAssociateDeassociateAssessorRequest()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        // Requests list
        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_REQUEST_PATH);

        $this->assertCount(0, $this->getEmailRepository()->findMessages(AssessorRequestAssociateMessage::class));

        $this->isSuccessful($this->client->getResponse());

        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_REQUEST, 2, 'à traiter');

        // Request page

        $linkNode = $crawler->filter('#request-link-be61ba07-c8e7-4533-97e1-0ab215cd752c');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        // I see request data
        self::assertSame('Demande d\'attribution', trim($crawler->filter('#request-title')->text()));
        self::assertSame('Demande en attente', trim($crawler->filter('.assessor-manager__request__col-left h4')->text()));
        $this->assertSameAssessorProfile(
            $crawler,
            [
                'author' => 'Madame Adrienne Kepoura',
                'email' => 'adrienne.kepoura@example.fr',
                'phone' => '+33 6 12 34 56 78',
                'birthdate' => '14/05/1973',
                'votePlaceWishes' => 'Salle Polyvalente De Wazemmes, Rue De L\'Abbé Aerts, 0113 Restaurant Scolaire - Rue H. Lefebvre, Groupe Scolaire Jean Zay, 0407',
                'office' => 'Suppléant',
                'voteCity' => 'Lille',
                'address' => '4 avenue du peuple Belge',
                'city' => '59000 Lille',
            ]
        );

        // I see request potential vote places
        $votePlaces = $crawler->filter('.datagrid__table tbody tr td.proxy_name strong');

        self::assertSame('Restaurant Scolaire - Rue H. Lefebvre, 0407', trim($votePlaces->first()->text()));

        // Associate the request with the vote place
        $linkNode = $crawler->filter('#associate-link-16');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        // I see assessor request data
        $this->assertSameAssessorProfile(
            $crawler,
            [
                'author' => 'Madame Adrienne Kepoura',
                'email' => 'adrienne.kepoura@example.fr',
                'phone' => '+33 6 12 34 56 78',
                'birthdate' => '14/05/1973',
                'votePlaceWishes' => 'Salle Polyvalente De Wazemmes, Rue De L\'Abbé Aerts, 0113 Restaurant Scolaire - Rue H. Lefebvre, Groupe Scolaire Jean Zay, 0407',
                'office' => 'Suppléant',
                'voteCity' => 'Lille',
                'address' => '4 avenue du peuple Belge',
                'city' => '59000 Lille',
            ]
        );

        // I see vote place data
        $this->assertSameVotePlaceProfile(
            $crawler,
            [
                'name' => 'Restaurant Scolaire - Rue H. Lefebvre',
                'address' => 'Groupe Scolaire Jean Zay',
                'postalCode' => '59350',
                'city' => 'Lille',
                'availableOffices' => ['Titulaire', 'Suppléant'],
            ]
        );

        $this->client->submit($crawler->filter('#confirm_action_allow')->form());

        $this->assertClientIsRedirectedTo(self::ASSESSOR_MANAGER_REQUEST_PATH.'/demande/be61ba07-c8e7-4533-97e1-0ab215cd752c', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        self::assertSame('Demande associée à Restaurant Scolaire - Rue H. Lefebvre', trim($crawler->filter('.assessor-manager__request__col-right h4')->text()));

        $this->assertCount(1, $this->getEmailRepository()->findMessages(AssessorRequestAssociateMessage::class));

        // Deassociate
        $linkNode = $crawler->filter('#request-deassociate');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        self::assertSame('Désassocier la demande du bureau de vote Restaurant Scolaire - Rue H. Lefebvre', trim($crawler->filter('.assessor-manager__content h3')->text()));

        // I see assessor request data
        $this->assertSameAssessorProfile(
            $crawler,
            [
                'author' => 'Madame Adrienne Kepoura',
                'email' => 'adrienne.kepoura@example.fr',
                'phone' => '+33 6 12 34 56 78',
                'birthdate' => '14/05/1973',
                'votePlaceWishes' => 'Salle Polyvalente De Wazemmes, Rue De L\'Abbé Aerts, 0113 Restaurant Scolaire - Rue H. Lefebvre, Groupe Scolaire Jean Zay, 0407',
                'office' => 'Suppléant',
                'voteCity' => 'Lille',
                'address' => '4 avenue du peuple Belge',
                'city' => '59000 Lille',
            ]
        );

        // I see vote place data
        $this->assertSameVotePlaceProfile(
            $crawler,
            [
                'name' => 'Restaurant Scolaire - Rue H. Lefebvre',
                'address' => 'Groupe Scolaire Jean Zay',
                'postalCode' => '59350',
                'city' => 'Lille',
                'availableOffices' => ['Titulaire'],
            ]
        );

        $this->client->submit($crawler->filter('#confirm_action_allow')->form());

        $this->assertClientIsRedirectedTo(self::ASSESSOR_MANAGER_REQUEST_PATH.'/demande/be61ba07-c8e7-4533-97e1-0ab215cd752c', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        self::assertSame('Demande en attente', trim($crawler->filter('.assessor-manager__request__col-left h4')->text()));

        $proxies = $crawler->filter('.datagrid__table tbody tr td.proxy_name strong');

        self::assertSame('Restaurant Scolaire - Rue H. Lefebvre, 0407', trim($proxies->first()->text()));
    }

    public function testAssessorManagerRequestsUnprocessedList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_REQUEST_PATH.'?status='.AssessorRequestFilters::UNPROCESSED);

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_REQUEST, 2, 'à traiter');
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Adrienne Kepoura")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Aubin Sahalor")'));
    }

    public function testAssessorManagerRequestsProcessedList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_REQUEST_PATH.'?status='.AssessorRequestFilters::PROCESSED);

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_REQUEST, 4, 'traitées');
        $this->assertCount(4, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Elise Coptère")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Prosper Hytté")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Ratif Luc")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Philippe Pélisson")'));
    }

    public function testAssessorManagerVotePlacesUnassociatedList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_VOTE_PLACE_PATH.'?status'.VotePlaceFilters::UNASSOCIATED);

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_VOTE_PLACE, 1, 'à remplir');
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Restaurant Scolaire")'));
    }

    public function testAssessorManagerRequestsDisabledList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_REQUEST_PATH.'?status='.AssessorRequestFilters::DISABLED);

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_REQUEST, 1, 'à traiter');
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Henri Cochet")'));
    }

    public function testAssessorManagerVotePlacesAssociatedList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_VOTE_PLACE_PATH.'?status='.VotePlaceFilters::ASSOCIATED);

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_VOTE_PLACE, 2, 'complet');
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Ecole Maternelle La Source")'));
    }

    public function testAssessorManagerUnprocessedRequestListsFilters()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_REQUEST_PATH);
        $this->isSuccessful($this->client->getResponse());

        $form = $crawler->selectButton('Filtrer')->form();

        $formValues = [
            AssessorRequestFilters::PARAMETER_LAST_NAME => null,
            AssessorRequestFilters::PARAMETER_VOTE_PLACE => null,
            AssessorRequestFilters::PARAMETER_CITY => null,
            AssessorRequestFilters::PARAMETER_COUNTRY => '',
        ];

        // Test last name filter
        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_LAST_NAME => 'Kepoura']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_LAST_NAME => 'Kepourapas']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test vote place wishes filter
        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_VOTE_PLACE => 'Salle']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_VOTE_PLACE => '59350_0113']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_VOTE_PLACE => 'Maternelle']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_VOTE_PLACE => '59000_0113']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test city filter
        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_CITY => 'Lille']));
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_CITY => 'Paris']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_CITY => '59350']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_CITY => '75010']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test country filter
        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_COUNTRY => 'FR']));
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_COUNTRY => 'AF']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test reset button
        $crawler = $this->client->click($crawler->selectLink('Réinitialiser le filtre')->link());
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testAssessorManagerProcessedRequestListsFilters()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_REQUEST_PATH.'?status='.AssessorRequestFilters::PROCESSED);
        $this->isSuccessful($this->client->getResponse());

        $form = $crawler->selectButton('Filtrer')->form();

        $formValues = [
            AssessorRequestFilters::PARAMETER_LAST_NAME => null,
            AssessorRequestFilters::PARAMETER_VOTE_PLACE => null,
            AssessorRequestFilters::PARAMETER_CITY => null,
            AssessorRequestFilters::PARAMETER_COUNTRY => '',
        ];

        // Test last name filter
        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_LAST_NAME => 'Coptère']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_LAST_NAME => 'Kepourapas']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test vote place wishes filter
        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_VOTE_PLACE => 'Ecole']));
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_VOTE_PLACE => '59350_0113']));
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_VOTE_PLACE => 'Paris']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_VOTE_PLACE => '59000_0113']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test city filter
        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_CITY => 'Bobigny']));
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_CITY => 'Paris']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_CITY => '93008']));
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_CITY => '75010']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test country filter
        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_COUNTRY => 'FR']));
        $this->assertCount(4, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_COUNTRY => 'AF']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test reset button
        $crawler = $this->client->click($crawler->selectLink('Réinitialiser le filtre')->link());
        $this->assertCount(4, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testAssessorManagerDisabledRequestListsFilters()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_REQUEST_PATH.'?status='.AssessorRequestFilters::DISABLED);
        $this->isSuccessful($this->client->getResponse());

        $form = $crawler->selectButton('Filtrer')->form();

        $formValues = [
            AssessorRequestFilters::PARAMETER_LAST_NAME => null,
            AssessorRequestFilters::PARAMETER_VOTE_PLACE => null,
            AssessorRequestFilters::PARAMETER_CITY => null,
            AssessorRequestFilters::PARAMETER_COUNTRY => '',
        ];

        // Test last name filter
        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_LAST_NAME => 'Cochet']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [AssessorRequestFilters::PARAMETER_LAST_NAME => 'Kepourapas']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test reset button
        $crawler = $this->client->click($crawler->selectLink('Réinitialiser le filtre')->link());
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testAssessorManagerUnassociatedVotePlaceListsFilters()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_VOTE_PLACE_PATH.'?status='.VotePlaceFilters::UNASSOCIATED);
        $this->isSuccessful($this->client->getResponse());

        $form = $crawler->selectButton('Filtrer')->form();

        $formValues = [
            VotePlaceFilters::PARAMETER_VOTE_PLACE => null,
            VotePlaceFilters::PARAMETER_CITY => null,
            VotePlaceFilters::PARAMETER_COUNTRY => '',
        ];

        // Test vote place wishes filter
        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_VOTE_PLACE => 'Salle']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_VOTE_PLACE => '59350_0113']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_VOTE_PLACE => 'Paris']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_VOTE_PLACE => '59000_0113']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test city filter
        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_CITY => 'Lille']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_CITY => 'Paris']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_CITY => '59350']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_CITY => '75010']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test country filter
        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_COUNTRY => 'FR']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_COUNTRY => 'AF']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test reset button
        $crawler = $this->client->click($crawler->selectLink('Réinitialiser le filtre')->link());
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testAssessorManagerAssociatedVotePlaceListsFilters()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_VOTE_PLACE_PATH.'?status='.VotePlaceFilters::ASSOCIATED);
        $this->isSuccessful($this->client->getResponse());

        $form = $crawler->selectButton('Filtrer')->form();

        $formValues = [
            VotePlaceFilters::PARAMETER_VOTE_PLACE => null,
            VotePlaceFilters::PARAMETER_CITY => null,
            VotePlaceFilters::PARAMETER_COUNTRY => '',
        ];

        // Test vote place wishes filter
        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_VOTE_PLACE => 'Ecole']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_VOTE_PLACE => '93066_0004']));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_VOTE_PLACE => 'Le Havre']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [VotePlaceFilters::PARAMETER_VOTE_PLACE => '59000_0113']));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        // Test reset button
        $crawler = $this->client->click($crawler->selectLink('Réinitialiser le filtre')->link());
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testAssessorManagerVotePlaceAssignedCitiesList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_VOTE_PLACE_CITIES_PATH.'?status='.CitiesFilters::ASSOCIATED);

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_VOTE_PLACE_CITIES, 2, 'avec au moins un assesseur');
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Lille (59000,59100)")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Saint-Denis (93200,93066)")'));
    }

    public function testCitiesAssignedAssessorsExport()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_VOTE_PLACE_CITIES_PATH.'?status='.CitiesFilters::ASSOCIATED);
        $this->isSuccessful($this->client->getResponse());

        $linkNode = $crawler->filter('#request-link-Lille');

        $this->assertCount(1, $linkNode);

        ob_start();
        $this->client->click($linkNode->link());
        $content = ob_get_clean();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $lines = $this->transformToArray($content);

        PHPUnitHelper::assertArraySubset(
            [
                'Numéro du bureau de vote',
                'Nom du bureau de vote',
                'Adresse postale du bureau de vote',
                'Nom assesseur titulaire',
                'Prénom assesseur titulaire',
                'Date de naissance assesseur titulaire',
                'Adresse postale assesseur titulaire',
                'Numéro de téléphone assesseur titulaire',
                'Numéro d\'électeur assesseur titulaire',
                'Nom assesseur suppléant',
                'Prénom assesseur suppléant',
                'Date de naissance assesseur suppléant',
                'Adresse postale assesseur suppléant',
                'Numéro de téléphone assesseur suppléant',
                'Numéro d\'électeur assesseur suppléant',
            ],
            $lines[0]
        );

        PHPUnitHelper::assertArraySubset(
            [
                '15',
                'Salle Polyvalente De Wazemmes 59350_0113',
                'Rue De L\'Abbé Aerts, 59000,59100 Lille FR',
                'Coptère',
                'Elise',
                '14/01/1986',
                'Pl. du Théâtre, 59000 Lille FR',
                '+33 6 12 34 56 78',
                '00004',
                'Pélisson',
                'Philippe',
                '29/03/1985',
                'Pl. du Théâtre, 59000 Lille FR',
                '+33 6 12 34 56 79',
                '00015',
            ],
            $lines[1]
        );

        $this->assertCount(2, $lines);
    }

    private function assertSameAssessorProfile(Crawler $crawler, array $profile): void
    {
        self::assertSame($profile['author'], trim($crawler->filter('#request-author')->text()));
        self::assertSame($profile['email'], trim($crawler->filter('#request-email')->text()));
        self::assertSame($profile['phone'], trim($crawler->filter('#request-phone')->text()));
        self::assertSame($profile['birthdate'], trim($crawler->filter('#request-birthdate')->text()));
        self::assertSame($profile['votePlaceWishes'], trim($crawler->filter('#request-vote-place-wishes')->text()));
        self::assertSame($profile['office'], trim($crawler->filter('#request-office')->text()));
        self::assertSame($profile['voteCity'], trim($crawler->filter('#request-vote-city')->text()));
        self::assertSame($profile['address'], trim($crawler->filter('#request-address')->text()));
        self::assertSame($profile['city'], trim($crawler->filter('#request-city')->text()));
    }

    private function assertSameVotePlaceProfile(Crawler $crawler, array $profile): void
    {
        self::assertSame($profile['name'], trim($crawler->filter('#vote-place-name')->text()));
        self::assertSame($profile['address'], trim($crawler->filter('#vote-place-address')->text()));
        self::assertSame($profile['postalCode'], trim($crawler->filter('#vote-place-postalCode')->text()));
        self::assertSame($profile['city'], trim($crawler->filter('#vote-place-city')->text()));

        foreach ($profile['availableOffices'] as $availableOffice) {
            $this->assertStringContainsString($availableOffice, trim($crawler->filter('#vote-place-available-offices')->html()));
        }
    }

    private function assertAssessorRequestTotalCount(
        Crawler $crawler,
        string $subject,
        int $count,
        string $status
    ): void {
        if (self::SUBJECT_REQUEST === $subject) {
            $message = $crawler->filter('.datagrid__pre-table.b__nudge--bottom-larger');
        } elseif (self::SUBJECT_VOTE_PLACE === $subject) {
            $message = $crawler->filter('.assessor_vote_places_total_count');
        } elseif (self::SUBJECT_VOTE_PLACE_CITIES === $subject) {
            $message = $crawler->filter('.assessor_cities_total_count');
        } else {
            throw new \InvalidArgumentException(sprintf('Expected one of "%s", but got "%s".', implode('", "', self::SUBJECTS), $subject));
        }

        $regexp = sprintf(
            'Vous avez %se? %s %ss?.',
            $count > 1 ? $count : 'un',
            $subject,
            $status
        );

        $this->assertCount(1, $message);
        $this->assertMatchesRegularExpression("/^$regexp\$/", trim($message->text()));
    }

    public function testAssessorRequestExport()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, self::ASSESSOR_MANAGER_VOTE_PLACE_PATH);
        $this->isSuccessful($this->client->getResponse());

        self::assertSame('Exporter les bureaux de vote traités', trim($crawler->filter('#vote-places-export')->text()));
        ob_start();
        $this->client->click($crawler->selectLink('Exporter les bureaux de vote traités')->link());
        $content = ob_get_clean();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $lines = $this->transformToArray($content);

        PHPUnitHelper::assertArraySubset(
            [
                'Ville du bureau de vote',
                'Nom du bureau vote',
                'Adresse du bureau de vote',
                'Pays du bureau de vote',
                'Fonction',
                'Genre',
                'Prénoms',
                'Nom',
                'Date de naissance',
                'Lieu de naissance',
                'Adresse',
                'Code postal',
                'Ville',
                "Ville du BV d'inscription sur les listes",
                "BV d'inscription sur les listes",
                'Adresse email',
                'Téléphone',
            ],
            $lines[0]
        );

        PHPUnitHelper::assertArraySubset(
            [
                'Saint-Denis',
                'Ecole Maternelle La Source',
                '15, Rue Auguste Blanqui',
                'France',
                'Titulaire',
                'Homme',
                'Ratif',
                'Luc',
                '04/02/1992',
                'Paris',
                '70 Rue Saint-Martin',
                '93008',
                'Paris',
                'Bobigny',
                '93008_0005',
                'luc.ratif@example.fr',
                '+33 6 12 34 56 78',
            ],
            $lines[2]
        );

        $this->assertCount(5, $lines);
    }
}
