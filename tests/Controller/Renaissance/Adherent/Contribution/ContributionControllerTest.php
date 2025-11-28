<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Adherent\Contribution;

use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use App\Repository\AdherentRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class ContributionControllerTest extends AbstractWebTestCase
{
    use ControllerTestTrait;

    private ?AdherentRepository $adherentRepository = null;

    public function testAnonymousCanNotAccessContributionWorkflow(): void
    {
        $this->client->request(Request::METHOD_GET, '/espace-elus/cotisation');
        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->assertClientIsRedirectedTo('/connexion', $this->client, true);
    }

    public function testNonElectedRepresentativeAdherentCanNotSeeFormations(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-elus/cotisation');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function testOnGoingElectedRepresentativeCanSeeContributionWorkflow(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email = 'gisele-berthoux@caramail.com');

        $this->assertInstanceOf(Adherent::class, $adherent);
        $this->assertNull($adherent->getLastContribution());

        $this->authenticateAsAdherent($this->client, $email);

        $this->client->request(Request::METHOD_GET, '/espace-elus/cotisation');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->submitForm('Valider', [
            'app_renaissance_contribution' => [
                'revenueAmount' => 0,
            ],
        ]);

        $this->assertStringContainsString('Pas de cotisation nécessaire', $crawler->filter('body')->text());

        $this->client->request(Request::METHOD_GET, '/espace-elus/cotisation?redeclare=1');

        $this->client->submitForm('Valider', [
            'app_renaissance_contribution' => [
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
            'app_renaissance_contribution' => [
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

        $adherent = $this->adherentRepository->findOneByEmail($email);

        $this->assertNotNull($adherent->getLastContribution());
        $this->assertInstanceOf(Contribution::class, $adherent->getLastContribution());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->adherentRepository = null;
    }
}
