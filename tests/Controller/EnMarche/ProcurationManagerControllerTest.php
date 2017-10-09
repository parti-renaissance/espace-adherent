<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadProcurationData;
use AppBundle\Procuration\Filter\ProcurationProxyProposalFilters;
use AppBundle\Procuration\Filter\ProcurationRequestFilters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class ProcurationManagerControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider providePages
     */
    public function testProcurationManagerBackendIsForbiddenAsAnonymous(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedToAuth();
    }

    /**
     * @dataProvider providePages
     */
    public function testProcurationManagerBackendIsForbiddenAsAdherentNotReferent(string $path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

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
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/demande/4');
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    public function testAssociateDeassociateRequest()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        // Requests list
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(3, $crawler->filter('.datagrid__table tbody tr'));

        // Request page
        $linkNode = $crawler->filter('#request-link-3');
        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

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
        $proxies = $crawler->filter('.datagrid__table tbody tr td strong');
        $this->assertSame('Maxime Michaux', trim($proxies->first()->text()));
        $this->assertSame('Jean-Michel Carbonneau', trim($proxies->last()->text()));

        // Associate the request with the proxy
        $linkNode = $crawler->filter('#associate-link-2');
        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

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
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame('Demande associée à Jean-Michel Carbonneau', trim($crawler->filter('.procuration-manager__request__col-right h4')->text()));

        // Deassociate
        $linkNode = $crawler->filter('#request-deassociate');
        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_deassociate]')->form());
        $this->assertClientIsRedirectedTo('/espace-responsable-procuration/demande/3', $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame('Demande en attente', trim($crawler->filter('.procuration-manager__request__col-left h4')->text()));

        $proxies = $crawler->filter('.datagrid__table tbody tr td strong');
        $this->assertSame('Maxime Michaux', trim($proxies->first()->text()));
        $this->assertSame('Jean-Michel Carbonneau', trim($proxies->last()->text()));
    }

    public function testProcurationManagerProxiesList()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Jean-Michel Carbonneau")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table td:contains("Maxime Michaux")'));
    }

    public function testFilterProcurationRequestsList()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(3, $crawler->filter('.datagrid__table tbody tr'));

        $formValues = [
            ProcurationRequestFilters::PARAMETER_COUNTRY => null,
            ProcurationRequestFilters::PARAMETER_CITY => null,
            ProcurationRequestFilters::PARAMETER_TYPE => null,
        ];

        $form = $crawler->selectButton('Filtrer')->form();
        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_COUNTRY => 'GB']));
        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_COUNTRY => 'FR']));
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => 'Paris']));
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => '75']));
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_TYPE => ProcurationRequestFilters::TYPE_LEGISLATIVE_1_ROUND]));
        $this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->click($crawler->selectLink('Annuler')->link());
        $this->assertCount(3, $crawler->filter('.datagrid__table tbody tr'));
    }

    public function testFilterProcurationProxyProposalsList()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $formValues = [
            ProcurationProxyProposalFilters::PARAMETER_COUNTRY => null,
            ProcurationProxyProposalFilters::PARAMETER_CITY => null,
            ProcurationProxyProposalFilters::PARAMETER_TYPE => null,
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

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => '18e']));
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => '75018']));
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));

        // Presidential first round is disabled at the moment
        //
        //$crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_TYPE => ProcurationProxyProposalFilters::TYPE_PRESIDENTIAL_1_ROUND]));
        //$this->assertCount(1, $crawler->filter('.datagrid__table tbody tr'));

        $crawler = $this->client->click($crawler->selectLink('Annuler')->link());
        $this->assertCount(2, $crawler->filter('.datagrid__table tbody tr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadHomeBlockData::class,
            LoadProcurationData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
