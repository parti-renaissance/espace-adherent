<?php

namespace Tests\App\Controller\Renaissance\ElectedRepresentative\Contribution;

use App\Entity\ElectedRepresentative\Contribution;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\AdherentRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ContributionControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private ?AdherentRepository $adherentRepository = null;
    private ?ElectedRepresentativeRepository $electedRepresentativeRepository = null;

    public function testAnonymousCanNotAccessContributionWorkflow(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-elus/cotisation');
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->assertClientIsRedirectedTo('/', $this->client);
    }

    public function testNonElectedRepresentativeAdherentCanNotSeeFormations(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-elus/cotisation');
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->assertClientIsRedirectedTo('/', $this->client);
    }

    public function testOnGoingElectedRepresentativeCanSeeContributionWorkflow(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('renaissance-user-2@en-marche-dev.fr');
        $electedRepresentative = $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent]);

        $this->assertInstanceOf(ElectedRepresentative::class, $electedRepresentative);
        $this->assertNull($electedRepresentative->getLastContribution());

        $this->authenticateAsAdherent($this->client, 'renaissance-user-2@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-elus/cotisation');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submitForm('Valider', [
            'app_renaissance_elected_representative_contribution' => [
                'revenueAmount' => 2000,
            ],
        ]);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-elus/cotisation/montant', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString('40€', $crawler->filter('#elected-representative-contribution')->text());

        $this->client->clickLink('Valider');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->submitForm('Valider', [
            'app_renaissance_elected_representative_contribution' => [
                'accountName' => 'John DOE',
                'accountCountry' => 'FR',
                'iban' => 'FR7630056009271234567890182',
            ],
        ]);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringContainsString(
            'Mandat de prélèvement complété',
            $crawler->filter('#elected-representative-contribution')->text()
        );

        $electedRepresentative = $this->electedRepresentativeRepository->findOneBy(['adherent' => $adherent]);

        $this->assertNotNull($electedRepresentative->getLastContribution());
        $this->assertInstanceOf(Contribution::class, $electedRepresentative->getLastContribution());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('renaissance_host'));

        $this->adherentRepository = $this->getAdherentRepository();
        $this->electedRepresentativeRepository = $this->getElectedRepresentativeRepository();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->adherentRepository = null;
        $this->electedRepresentativeRepository = null;
    }
}
