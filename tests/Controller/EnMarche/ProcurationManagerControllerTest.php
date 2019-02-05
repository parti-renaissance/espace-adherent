<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadElectionData;
use AppBundle\DataFixtures\ORM\LoadProcurationData;
use AppBundle\Procuration\Filter\ProcurationProxyProposalFilters;
use AppBundle\Procuration\Filter\ProcurationRequestFilters;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group procuration
 */
class ProcurationManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private const SUBJECT_REQUEST = 'demandes? de procuration';
    private const SUBJECT_PROPOSAL = 'propositions? de mandataires?';
    private const SUBJECTS = [
        self::SUBJECT_REQUEST,
        self::SUBJECT_PROPOSAL,
    ];

    /**
     * @dataProvider providePages
     */
    public function testProcurationManagerBackendIsForbiddenAsAnonymous(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testProcurationManagerBackendIsForbiddenAsAdherentNotReferent(string $path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function providePages()
    {
        return [
            ['/espace-responsable-procuration'],
            ['/espace-responsable-procuration/mandataires'],
            ['/espace-responsable-procuration/demande/1'],
            ['/espace-responsable-procuration/demande/2'],
        ];
    }

    public function testProcurationManagerNotManagedRequestIsForbidden()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/demande/4');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testAssociateDeassociateRequest()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        // Requests list
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration');

        $this->isSuccessful($this->client->getResponse());

        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_REQUEST, 2, 'à traiter');

        // Request page
        $linkNode = $crawler->filter('#request-link-6');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        // I see request data
        $this->assertSame('Demande de procuration n°6', trim($crawler->filter('#request-title')->text()));
        $this->assertSame('Demande en attente', trim($crawler->filter('.procuration-manager__request__col-left h4')->text()));
        $this->assertSame('Madame Belle, Carole D\'Aubigné', trim($crawler->filter('#request-author')->text()));
        $this->assertSame('belle.carole@example.fr', trim($crawler->filter('#request-email')->text()));
        $this->assertSame('+33 6 55 44 33 22', trim($crawler->filter('#request-phone')->text()));
        $this->assertSame('09/03/1978', trim($crawler->filter('#request-birthdate')->text()));
        $this->assertSame('75010 Paris 10e FR', trim($crawler->filter('#request-vote-city')->text()));
        $this->assertSame('Madeleine', trim($crawler->filter('#request-vote-office')->text()));
        $this->assertSame('77, Place de la Madeleine', trim($crawler->filter('#request-address')->text()));
        $this->assertSame('75010 Paris 10e FR', trim($crawler->filter('#request-city')->text()));
        $this->assertSame('Pour raison de santé', trim($crawler->filter('#request-reason')->text()));

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/demande/3');

        $this->isSuccessful($this->client->getResponse());

        // I see request data
        $this->assertSame('Demande de procuration n°3', trim($crawler->filter('#request-title')->text()));
        $this->assertSame('Demande en attente', trim($crawler->filter('.procuration-manager__request__col-left h4')->text()));
        $this->assertSame('Madame Fleur Paré', trim($crawler->filter('#request-author')->text()));
        $this->assertSame('FleurPare@armyspy.com', trim($crawler->filter('#request-email')->text()));
        $this->assertSame('+33 1 69 64 10 61', trim($crawler->filter('#request-phone')->text()));
        $this->assertSame('29/01/1945', trim($crawler->filter('#request-birthdate')->text()));
        $this->assertSame('75018 Paris 18e FR', trim($crawler->filter('#request-vote-city')->text()));
        $this->assertSame('Aquarius', trim($crawler->filter('#request-vote-office')->text()));
        $this->assertSame('13, rue Reine Elisabeth', trim($crawler->filter('#request-address')->text()));
        $this->assertSame('77000 Melun FR', trim($crawler->filter('#request-city')->text()));
        $this->assertSame('Pour raison de santé', trim($crawler->filter('#request-reason')->text()));

        // I see request potential proxies
        $proxies = $crawler->filter('.datagrid__table tbody tr td.proxy_name strong');

        $this->assertSame('Jean-Michel Carbonneau', trim($proxies->first()->text()));
        $this->assertSame('Maxime Michaux', trim($proxies->last()->text()));

        // Associate the request with the proxy
        $linkNode = $crawler->filter('#associate-link-2');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        // I see proxy data
        $this->assertSame('Monsieur Jean-Michel Carbonneau', trim($crawler->filter('#proxy-author')->text()));
        $this->assertSame('jm.carbonneau@example.fr', trim($crawler->filter('#proxy-email')->text()));
        $this->assertSame('+33 9 88 77 66 55', trim($crawler->filter('#proxy-phone')->text()));
        $this->assertSame('17/01/1974', trim($crawler->filter('#proxy-birthdate')->text()));
        $this->assertSame('75018 Paris 18e FR', trim($crawler->filter('#proxy-vote-city')->text()));
        $this->assertSame('Lycée général Zola', trim($crawler->filter('#proxy-vote-office')->text()));
        $this->assertSame('14 rue Jules Ferry', trim($crawler->filter('#proxy-address')->text()));
        $this->assertSame('75018 Paris 20e FR', trim($crawler->filter('#proxy-city')->text()));

        $this->client->submit($crawler->filter('form[name=app_associate]')->form());

        $this->assertClientIsRedirectedTo('/espace-responsable-procuration/demande/3', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Demande associée à Jean-Michel Carbonneau', trim($crawler->filter('.procuration-manager__request__col-right h4')->text()));

        // Deassociate
        $linkNode = $crawler->filter('#request-deassociate');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_deassociate]')->form());

        $this->assertClientIsRedirectedTo('/espace-responsable-procuration/demande/3', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Demande en attente', trim($crawler->filter('.procuration-manager__request__col-left h4')->text()));

        $proxies = $crawler->filter('.datagrid__table tbody tr td.proxy_name strong');

        $this->assertSame('Jean-Michel Carbonneau', trim($proxies->first()->text()));
        $this->assertSame('Maxime Michaux', trim($proxies->last()->text()));
    }

    public function testProcurationManagerProxiesList()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_PROPOSAL, 2, 'disponible');
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Léa Bouquet")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Emmanuel Harquin")'));
    }

    public function testProcurationManagerProxiesListAssociated()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires?status=associated');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_PROPOSAL, 1, 'associée');
        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Romain Gentil")'));
    }

    public function testFilterProcurationRequestsList()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_REQUEST, 2, 'à traiter');
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $formValues = [
            ProcurationRequestFilters::PARAMETER_COUNTRY => null,
            ProcurationRequestFilters::PARAMETER_CITY => null,
            ProcurationRequestFilters::PARAMETER_ELECTION_ROUND => null,
        ];

        $form = $crawler->selectButton('Filtrer')->form();
        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_COUNTRY => 'GB']));

        $this->assertCount(0, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_COUNTRY => 'FR']));

        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => 'Paris']));

        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => '75']));

        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => '75010']));

        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => '75020']));

        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_ELECTION_ROUND => 5]));

        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->click($crawler->selectLink('Annuler')->link());

        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));
    }

    public function testProcurationManagerRequestsListProcessed()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration?status=processed');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_REQUEST, 1, 'traitée');
        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Alice Delavega")'));
    }

    public function testFilterProcurationProxyProposalsList()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_PROPOSAL, 2, 'disponible');
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $formValues = [
            ProcurationProxyProposalFilters::PARAMETER_COUNTRY => null,
            ProcurationProxyProposalFilters::PARAMETER_CITY => null,
            ProcurationProxyProposalFilters::PARAMETER_ELECTION_ROUND => null,
        ];

        $form = $crawler->selectButton('Filtrer')->form();
        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_COUNTRY => 'GB']));

        $this->assertCount(0, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_COUNTRY => 'FR']));

        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => 'Nantes']));

        $this->assertCount(0, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => '44']));

        $this->assertCount(0, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => '10e']));

        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => '75010']));

        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_ELECTION_ROUND => 6]));

        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->click($crawler->selectLink('Annuler')->link());

        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadElectionData::class,
            LoadProcurationData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }

    private function assertProcurationTotalCount(Crawler $crawler, string $subject, int $count, string $status): void
    {
        if (self::SUBJECT_REQUEST === $subject) {
            $message = $crawler->filter('.procuration_requests_total_count');
        } elseif (self::SUBJECT_PROPOSAL === $subject) {
            $message = $crawler->filter('.procuration_proposals_total_count');
        } else {
            throw new \InvalidArgumentException(sprintf('Expected one of "%s" or "%s", but got "%s".', implode('", "', self::SUBJECTS), $subject));
        }

        $regexp = sprintf(
            'Vous avez %s %s %ss?.',
            $count > 1 ? $count : 'une',
            $subject,
            $status
        );

        $this->assertCount(1, $message);
        $this->assertRegExp("/^$regexp\$/", trim($message->text()));
    }
}
