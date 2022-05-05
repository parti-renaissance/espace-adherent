<?php

namespace Tests\App\Controller\EnMarche;

use App\Procuration\Filter\ProcurationProxyProposalFilters;
use App\Procuration\Filter\ProcurationRequestFilters;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

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

    public function providePages(): array
    {
        return [
            ['/espace-responsable-procuration'],
            ['/espace-responsable-procuration/mandataires'],
            ['/espace-responsable-procuration/demande/1'],
            ['/espace-responsable-procuration/demande/2'],
            ['/espace-responsable-procuration/mandataires/1'],
            ['/espace-responsable-procuration/mandataires/4'],
            ['/espace-responsable-procuration/mandataires/7'],
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

        $this->assertCount(5, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_REQUEST, 5, 'à traiter');

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

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/demande/12');

        $this->isSuccessful($this->client->getResponse());

        // I see request data
        $this->assertSame('Demande de procuration n°12', trim($crawler->filter('#request-title')->text()));
        $this->assertSame('Demande en attente', trim($crawler->filter('.procuration-manager__request__col-left h4')->text()));
        $this->assertSame('Monsieur Jean-Michel Amoitié', trim($crawler->filter('#request-author')->text()));
        $this->assertSame('jeanmichel.amoitié@example.es', trim($crawler->filter('#request-email')->text()));
        $this->assertSame('+44 7911 123457', trim($crawler->filter('#request-phone')->text()));
        $this->assertSame('20/12/1989', trim($crawler->filter('#request-birthdate')->text()));
        $this->assertSame('GV6H  GB', trim($crawler->filter('#request-vote-city')->text()));
        $this->assertSame('Camden', trim($crawler->filter('#request-vote-office')->text()));
        $this->assertSame('4Q Covent Garden', trim($crawler->filter('#request-address')->text()));
        $this->assertSame('GV6H  GB', trim($crawler->filter('#request-city')->text()));
        $this->assertSame('Pour raison de santé', trim($crawler->filter('#request-reason')->text()));

        // I see request potential proxies
        $proxies = $crawler->filter('.datagrid__table tbody tr');

        $this->assertSame('Jean-Marc Gastro', trim($proxies->first()->filter('td.proxy_name strong')->text()));
        $this->assertStringContainsString('Plutôt fiable', trim($proxies->eq(1)->text()));

        // Associate the request with the proxy
        $linkNode = $crawler->filter('#associate-link-8');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        // I see proxy data
        $this->assertSame('Monsieur Jean-Marc Gastro', trim($crawler->filter('#proxy-author')->text()));
        $this->assertSame('jeanmarc.gastro@example.es', trim($crawler->filter('#proxy-email')->text()));
        $this->assertSame('+44 7911 123465', trim($crawler->filter('#proxy-phone')->text()));
        $this->assertSame('21/12/1989', trim($crawler->filter('#proxy-birthdate')->text()));
        $this->assertSame('GV6H  GB', trim($crawler->filter('#proxy-vote-city')->text()));
        $this->assertSame('Camden', trim($crawler->filter('#proxy-vote-office')->text()));
        $this->assertSame('4Q Covent Garden', trim($crawler->filter('#proxy-address')->text()));
        $this->assertSame('GV6H  GB', trim($crawler->filter('#proxy-city')->text()));

        $this->client->submit($crawler->filter('form[name=app_associate]')->form());

        $this->assertClientIsRedirectedTo('/espace-responsable-procuration/demande/12', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Demande associée à Jean-Marc Gastro', trim($crawler->filter('.procuration-manager__request__col-right h4')->text()));

        // Deassociate
        $linkNode = $crawler->filter('#request-deassociate');

        $this->assertCount(1, $linkNode);

        $crawler = $this->client->click($linkNode->link());

        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_deassociate]')->form());

        $this->assertClientIsRedirectedTo('/espace-responsable-procuration/demande/12', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Demande en attente', trim($crawler->filter('.procuration-manager__request__col-left h4')->text()));

        $proxies = $crawler->filter('.datagrid__table tbody tr td.proxy_name strong');

        $this->assertSame('Jean-Marc Gastro', trim($proxies->first()->text()));
    }

    public function testProcurationManagerProxiesList()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_PROPOSAL, 3, 'disponible');
        $this->assertCount(3, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Léa Bouquet")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Emmanuel Harquin")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Jean-Marc Gastro")'));
    }

    public function testProcurationManagerProxiesListAssociated()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires?status=associated');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_PROPOSAL, 1, 'associée');
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Romain Gentil")'));
    }

    public function testFilterProcurationRequestsList()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_REQUEST, 5, 'à traiter');
        $this->assertCount(5, $crawler->filter('.datagrid__table-manager tbody tr'));

        $formValues = [
            ProcurationRequestFilters::PARAMETER_COUNTRY => null,
            ProcurationRequestFilters::PARAMETER_CITY => null,
            ProcurationRequestFilters::PARAMETER_ELECTION_ROUND => null,
        ];

        $form = $crawler->selectButton('Filtrer')->form();
        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_COUNTRY => 'GB']));

        $this->assertCount(3, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_COUNTRY => 'FR']));

        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => 'Paris']));

        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => '75']));

        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => '75010']));

        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_CITY => '75020']));

        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_ELECTION_ROUND => 5]));

        $this->assertCount(4, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_LAST_NAME => 'Amoitie']));

        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_LAST_NAME => 'moitié']));

        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationRequestFilters::PARAMETER_LAST_NAME => 'Tran']));

        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->click($crawler->selectLink('Annuler')->link());

        $this->assertCount(5, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testProcurationManagerRequestsListProcessed()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration?status=processed');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_REQUEST, 2, 'traitée');
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Alice Delavega")'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Jean Dell")'));
    }

    public function testProcurationManagerRequestsListDisabled()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration?status=disabled');

        $this->isSuccessful($this->client->getResponse());
        $this->assertStringContainsString('Néanmoins certains mandants ont été désactivés de manière automatique pour des raisons de sécurité, merci de ne pas les réactiver.', trim($crawler->filter('.procuration-manager div.alert--tips')->text()));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Jean Désactivé")'));
    }

    public function testFilterProcurationProxyProposalsList()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_PROPOSAL, 3, 'disponible');
        $this->assertCount(3, $crawler->filter('.datagrid__table-manager tbody tr'));

        $formValues = [
            ProcurationProxyProposalFilters::PARAMETER_COUNTRY => null,
            ProcurationProxyProposalFilters::PARAMETER_CITY => null,
            ProcurationProxyProposalFilters::PARAMETER_ELECTION_ROUND => null,
            ProcurationProxyProposalFilters::PARAMETER_LAST_NAME => null,
        ];

        $form = $crawler->selectButton('Filtrer')->form();
        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_COUNTRY => 'GB']));

        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_COUNTRY => 'FR']));

        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => 'Nantes']));

        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => '44']));

        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => '10e']));

        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_CITY => '75010']));

        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_ELECTION_ROUND => 6]));

        $this->assertCount(3, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_LAST_NAME => 'harquin']));

        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_LAST_NAME => 'Harq']));

        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->submit($form, array_merge($formValues, [ProcurationProxyProposalFilters::PARAMETER_LAST_NAME => 'Tran']));

        $this->assertCount(0, $crawler->filter('.datagrid__table-manager tbody tr'));

        $crawler = $this->client->click($crawler->selectLink('Annuler')->link());

        $this->assertCount(3, $crawler->filter('.datagrid__table-manager tbody tr'));
    }

    public function testProcurationManagerProxiesListDisabled()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires?status=disabled');

        $this->isSuccessful($this->client->getResponse());
        $this->assertProcurationTotalCount($crawler, self::SUBJECT_PROPOSAL, 1, 'désactivée');
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Annie Versaire")'));
    }

    public function testSeeProcurationProxyInformation()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration/mandataires/1');

        $this->isSuccessful($this->client->getResponse());

        $this->assertSame('Mandataire n°1', trim($crawler->filter('#proxy-title')->text()));
        $this->assertStringContainsString('Disponible', trim($crawler->filter('.procuration-manager__proxy h4')->text()));
        $this->assertSame('Monsieur Maxime Michaux', trim($crawler->filter('#proxy-author')->text()));
        $this->assertSame('maxime.michaux@example.fr', trim($crawler->filter('#proxy-email')->text()));
        $this->assertSame('+33 9 88 77 66 55', trim($crawler->filter('#proxy-phone')->text()));
        $this->assertSame('17/02/1989', trim($crawler->filter('#proxy-birthdate')->text()));
        $this->assertSame('123456789', trim($crawler->filter('#proxy-voter-number')->text()));
        $this->assertSame('75018 Paris 18e FR', trim($crawler->filter('#proxy-vote-city')->text()));
        $this->assertSame('Mairie', trim($crawler->filter('#proxy-vote-office')->text()));
        $this->assertSame('14 rue Jules Ferry', trim($crawler->filter('#proxy-address')->text()));
        $this->assertSame('75018 Paris 18e FR', trim($crawler->filter('#proxy-city')->text()));
        $this->assertCount(2, $rounds = $crawler->filter('#proxy-election-rounds > div > ul > li'));

        $firstRound = $rounds->eq(0);
        $secondRound = $rounds->eq(1);

        $this->assertStringContainsString('2e tour des éléctions présidentielles 2017', trim($firstRound->text()));
        $this->assertStringContainsString('2e tour des éléctions législatives 2017', trim($secondRound->text()));
        $this->assertSame('en France : Oui', trim($firstRound->filter('li.proxy-french-request-available')->text()));
        $this->assertSame('à l\'étranger : Oui', trim($firstRound->filter('li.proxy-foreign-request-available')->text()));
        $this->assertSame('en France : Oui', trim($secondRound->filter('li.proxy-french-request-available')->text()));
        $this->assertSame('à l\'étranger : Oui', trim($secondRound->filter('li.proxy-foreign-request-available')->text()));
    }

    public function testProcurationManagerDisableEnableRequest()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration?status=disabled');

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager td:contains("Jean-Michel Amoitié")'));

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration?status=unprocessed');

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(5, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $toDisable = $crawler->filter('.datagrid__table-manager td:contains("Jean-Michel Amoitié")'));
        $this->assertCount(1, $linkToDisable = $toDisable->closest('tr')->filter('a:contains("Désactiver")'));

        // disable
        $this->client->click($linkToDisable->link());

        $this->assertResponseStatusCode(302, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-responsable-procuration', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(4, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager td:contains("Jean-Michel Amoitié")'));

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration?status=disabled');

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(2, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $toEnable = $crawler->filter('.datagrid__table-manager td:contains("Jean-Michel Amoitié")'));
        $this->assertStringContainsString('Manuellement', $toEnable->closest('tr')->text());
        $this->assertCount(1, $linkToEnable = $toEnable->closest('tr')->filter('a:contains("Réactiver")'));

        // enable
        $this->client->click($linkToEnable->link());

        $this->assertResponseStatusCode(302, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-responsable-procuration', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(5, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager td:contains("Jean-Michel Amoitié")'));

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-responsable-procuration?status=disabled');

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(1, $crawler->filter('.datagrid__table-manager tbody tr'));
        $this->assertCount(0, $crawler->filter('.datagrid__table-manager td:contains("Jean-Michel Amoitié")'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }

    private function assertProcurationTotalCount(Crawler $crawler, string $subject, int $count, string $status): void
    {
        if (self::SUBJECT_REQUEST === $subject) {
            $message = $crawler->filter('.procuration_requests_total_count');
        } elseif (self::SUBJECT_PROPOSAL === $subject) {
            $message = $crawler->filter('.procuration_proposals_total_count');
        } else {
            throw new \InvalidArgumentException(sprintf('Expected one of "%s", but got "%s".', implode('", "', self::SUBJECTS), $subject));
        }

        $regexp = sprintf(
            'Vous avez %s %s %ss?.',
            $count > 1 ? $count : 'une',
            $subject,
            $status
        );

        $this->assertCount(1, $message);
        $this->assertMatchesRegularExpression("/^$regexp\$/", trim($message->text()));
    }
}
