<?php

namespace Tests\AppBundle\Controller\EnMarche;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group assessor
 */
class AssessorManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private const ASSESSOR_MANAGER_EMAIL = 'commissaire.biales@example.fr';
    private const SUBJECT_REQUEST = 'demandes d\'assesseur';
    private const SUBJECT_VOTE_PLACE = 'bureaux? de vote';
    private const SUBJECTS = [
        self::SUBJECT_REQUEST,
        self::SUBJECT_VOTE_PLACE,
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    /**
     * @dataProvider providePages
     */
    public function testAssessorManagerBackendIsForbiddenAsAnonymous(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testAssessorManagerBackendIsForbiddenAsAdherentNotReferent(string $path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function providePages(): \Generator
    {
        yield ['/espace-responsable-assesseur'];
        yield ['/espace-responsable-assesseur/demande/be61ba07-c8e7-4533-97e1-0ab215cd752c'];
        yield ['/espace-responsable-assesseur/vote-places'];
    }

    public function testAssessorManagerBackendIsForbiddenOnWrongArea()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $this->client->request(Request::METHOD_GET, '/espace-responsable-assesseur/demande/d320b698-10b7-4dd7-a70a-cedb95fceeda');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        $this->client->request(Request::METHOD_GET, '/espace-responsable-assesseur/demande/f9286607-c3b5-4531-be03-81c3fb4fafe8');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function testAssociateDeassociateAssessorRequest()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        // Requests list
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-assesseur');

        $this->isSuccessful($this->client->getResponse());

        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_REQUEST, 2, 'à traiter');

        // Request page

        $linkNode = $crawler->filter('#request-link-be61ba07-c8e7-4533-97e1-0ab215cd752c');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        // I see request data
        $this->assertSame('Demande d\'attribution', trim($crawler->filter('#request-title')->text()));
        $this->assertSame('Demande en attente', trim($crawler->filter('.assessor-manager__request__col-left h4')->text()));
        $this->assertSameAssessorProfile(
            $crawler,
            [
                'author' => 'Madame Adrienne Kepoura',
                'email' => 'adrienne.kepoura@example.fr',
                'phone' => '+33 6 12 34 56 78',
                'birthdate' => '14/05/1973',
                'votePlaceWishes' => 'Salle Polyvalente De WazemmesRestaurant Scolaire - Rue H. Lefebvre',
                'office' => 'Suppléant',
                'voteCity' => 'Lille',
                'address' => '4 avenue du peuple Belge',
                'city' => '59000 Lille',
            ]
        );

        // I see request potential vote places
        $votePlaces = $crawler->filter('.datagrid__table tbody tr td.proxy_name strong');

        $this->assertSame('Restaurant Scolaire - Rue H. Lefebvre', trim($votePlaces->first()->text()));

        // Associate the request with the vote place
        $linkNode = $crawler->filter('#associate-link-2');

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
                'votePlaceWishes' => 'Salle Polyvalente De WazemmesRestaurant Scolaire - Rue H. Lefebvre',
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
                'availableOFfices' => "Titulaire\nSuppléant",
            ]
        );

        $this->client->submit($crawler->filter('#confirm_action_allow')->form());

        $this->assertClientIsRedirectedTo('/espace-responsable-assesseur/demande/be61ba07-c8e7-4533-97e1-0ab215cd752c', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Demande associée à Restaurant Scolaire - Rue H. Lefebvre', trim($crawler->filter('.assessor-manager__request__col-right h4')->text()));

        // Deassociate
        $linkNode = $crawler->filter('#request-deassociate');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Désassocier la demande du bureau de vote Restaurant Scolaire - Rue H. Lefebvre', trim($crawler->filter('.assessor-manager__content h3')->text()));

        // I see assessor request data
        $this->assertSameAssessorProfile(
            $crawler,
            [
                'author' => 'Madame Adrienne Kepoura',
                'email' => 'adrienne.kepoura@example.fr',
                'phone' => '+33 6 12 34 56 78',
                'birthdate' => '14/05/1973',
                'votePlaceWishes' => 'Salle Polyvalente De WazemmesRestaurant Scolaire - Rue H. Lefebvre',
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
                'availableOFfices' => 'Titulaire',
            ]
        );

        $this->client->submit($crawler->filter('#confirm_action_allow')->form());

        $this->assertClientIsRedirectedTo('/espace-responsable-assesseur/demande/be61ba07-c8e7-4533-97e1-0ab215cd752c', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Demande en attente', trim($crawler->filter('.assessor-manager__request__col-left h4')->text()));

        $proxies = $crawler->filter('.datagrid__table tbody tr td.proxy_name strong');

        $this->assertSame('Restaurant Scolaire - Rue H. Lefebvre', trim($proxies->first()->text()));
    }

    public function testAssessorManagerRequestsUnprocessedList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-assesseur?status=unprocessed');

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_REQUEST, 2, 'à traiter');
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Adrienne Kepoura")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Aubin Sahalor")'));
    }

    public function testAssessorManagerRequestsProcessedList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-assesseur?status=processed');

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_REQUEST, 3, 'traitées');
        $this->assertCount(3, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Elise Coptère")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Prosper Hytté")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Ratif Luc")'));
    }

    public function testAssessorManagerVotePlacesUnassociatedList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-assesseur/vote-places?status=unassociated');

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_VOTE_PLACE, 2, 'à remplir');
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Salle Polyvalente De Wazemmes")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Restaurant Scolaire")'));
    }

    public function testAssessorManagerVotePlacesAssociatedList()
    {
        $this->authenticateAsAdherent($this->client, self::ASSESSOR_MANAGER_EMAIL);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-assesseur/vote-places?status=associated');

        $this->isSuccessful($this->client->getResponse());

        $this->assertAssessorRequestTotalCount($crawler, self::SUBJECT_VOTE_PLACE, 1, 'complet');
        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Ecole Maternelle La Source")'));
    }

    private function assertSameAssessorProfile(Crawler $crawler, array $profile): void
    {
        $this->assertSame($profile['author'], trim($crawler->filter('#request-author')->text()));
        $this->assertSame($profile['email'], trim($crawler->filter('#request-email')->text()));
        $this->assertSame($profile['phone'], trim($crawler->filter('#request-phone')->text()));
        $this->assertSame($profile['birthdate'], trim($crawler->filter('#request-birthdate')->text()));
        $this->assertSame($profile['votePlaceWishes'], trim($crawler->filter('#request-vote-place-wishes')->text()));
        $this->assertSame($profile['office'], trim($crawler->filter('#request-office')->text()));
        $this->assertSame($profile['voteCity'], trim($crawler->filter('#request-vote-city')->text()));
        $this->assertSame($profile['address'], trim($crawler->filter('#request-address')->text()));
        $this->assertSame($profile['city'], trim($crawler->filter('#request-city')->text()));
    }

    private function assertSameVotePlaceProfile(Crawler $crawler, array $profile): void
    {
        $this->assertSame($profile['name'], trim($crawler->filter('#vote-place-name')->text()));
        $this->assertSame($profile['address'], trim($crawler->filter('#vote-place-address')->text()));
        $this->assertSame($profile['postalCode'], trim($crawler->filter('#vote-place-postalCode')->text()));
        $this->assertSame($profile['city'], trim($crawler->filter('#vote-place-city')->text()));
        $this->assertSame($profile['availableOFfices'], trim($crawler->filter('#vote-place-available-offices')->text()));
    }

    private function assertAssessorRequestTotalCount(
        Crawler $crawler,
        string $subject,
        int $count,
        string $status
    ): void {
        if (self::SUBJECT_REQUEST === $subject) {
            $message = $crawler->filter('.assessor_requests_total_count');
        } elseif (self::SUBJECT_VOTE_PLACE === $subject) {
            $message = $crawler->filter('.assessor_vote_places_total_count');
        } else {
            throw new \InvalidArgumentException(sprintf('Expected one of "%s" or "%s", but got "%s".', implode('", "', self::SUBJECTS), $subject));
        }

        $regexp = sprintf(
            'Vous avez %s %s %ss?.',
            $count > 1 ? $count : 'un',
            $subject,
            $status
        );

        $this->assertCount(1, $message);
        $this->assertRegExp("/^$regexp\$/", trim($message->text()));
    }
}
