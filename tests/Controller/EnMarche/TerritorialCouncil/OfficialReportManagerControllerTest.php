<?php

namespace Tests\App\Controller\EnMarche\TerritorialCouncil;

use App\Entity\Report\Report;
use App\Entity\TerritorialCouncil\OfficialReport;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class OfficialReportManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideAdherentsWithNoAccess
     */
    public function testCannotListOfficialReports(string $adherentEmail)
    {
        $this->authenticateAsAdherent($this->client, $adherentEmail);

        $this->client->request(Request::METHOD_GET, '/espace-referent/instances/proces-verbaux');

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testListOfficialReports()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/instances/proces-verbaux');

        self::assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        self::assertCount(2, $crawler->filter('tbody tr.official-report'));
        self::assertContains('CoPol de Paris (75)', $crawler->filter('tbody tr.official-report')->eq(0)->filter('td')->eq(0)->text());
        self::assertContains('Deuxième PV 75', $crawler->filter('tbody tr.official-report')->eq(0)->filter('td')->eq(1)->text());
        self::assertContains('15/10/2020 15:15', $crawler->filter('tbody tr.official-report')->eq(0)->filter('td')->eq(2)->text());
        self::assertContains('20/10/2020 10:20', $crawler->filter('tbody tr.official-report')->eq(0)->filter('td')->eq(3)->text());
        self::assertContains('2', $crawler->filter('tbody tr.official-report')->eq(0)->filter('td')->eq(4)->text());
        self::assertContains('Referent Referent', $crawler->filter('tbody tr.official-report')->eq(0)->filter('td')->eq(5)->text());
    }

    /**
     * @dataProvider provideAdherentsWithNoAccess
     */
    public function testCannotCreateOfficialReport(string $adherentEmail)
    {
        $this->authenticateAsAdherent($this->client, $adherentEmail);

        $this->client->request(Request::METHOD_GET, '/espace-referent/instances/proces-verbaux/creer');

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCreateOfficialReport()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/instances/proces-verbaux');

        self::assertCount(2, $crawler->filter('tbody tr.official-report'));

        $creationLink = $crawler->selectLink('Créer un procès-verbal');

        self::assertCount(1, $creationLink);

        $this->client->click($creationLink->link());

        $this->assertEquals('http://'.$this->hosts['app'].'/espace-referent/instances/proces-verbaux/creer', $this->client->getRequest()->getUri());
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    /**
     * @dataProvider provideAdherentsWithNoEditRight
     */
    public function testCannotUpdateOfficialReport(string $adherentEmail)
    {
        $this->authenticateAsAdherent($this->client, $adherentEmail);

        /** @var Report $report */
        $report = $this->getOfficialReportRepository()->findOneBy(['name' => 'Test PV 75 1']);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/espace-referent/instances/proces-verbaux/%s/modifier', $report->getUuid()));

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testUpdateOfficialReport()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/instances/proces-verbaux');

        self::assertCount(2, $crawler->filter('tbody tr.official-report'));
        self::assertContains('CoPol de Paris (75)', $crawler->filter('tbody tr.official-report')->eq(1)->filter('td')->eq(0)->text());
        self::assertContains('10/10/2020 10:10', $crawler->filter('tbody tr.official-report')->eq(1)->filter('td')->eq(2)->text());
        self::assertContains('10/10/2020 10:10', $crawler->filter('tbody tr.official-report')->eq(1)->filter('td')->eq(3)->text());
        self::assertContains('1', $crawler->filter('tbody tr.official-report')->eq(1)->filter('td')->eq(4)->text());
        self::assertContains('Referent Referent', $crawler->filter('tbody tr.official-report')->eq(1)->filter('td')->eq(5)->text());

        $creationLink = $crawler->selectLink('Modifier');

        self::assertCount(2, $creationLink);

        $crawler = $this->client->click($creationLink->eq(1)->link());

        /** @var OfficialReport $report */
        $report = $this->getOfficialReportRepository()->findOneBy(['name' => 'Test PV 75 1']);

        $this->assertEquals(
            \sprintf('http://%s/espace-referent/instances/proces-verbaux/%s/modifier', $this->hosts['app'], $report->getUuid()),
            $this->client->getRequest()->getUri()
        );
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $politicalCommittee = $this->getPoliticalCommitteeRepository()->findOneBy(['name' => 'CoPol de Paris (75)']);
        $files = [
            'official_report' => [
                'error' => ['file' => \UPLOAD_ERR_OK],
                'name' => ['file' => 'pv.pdf'],
                'size' => ['file' => 631],
                'tmp_name' => ['file' => __DIR__.'/../../../Fixtures/document.pdf'],
                'type' => ['file' => 'application/pdf'],
            ],
        ];

        $form = $crawler->filter('form[name=official_report]')->form();
        $this->client->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), $files);

        self::assertClientIsRedirectedTo('/espace-referent/instances/proces-verbaux', $this->client);

        $crawler = $this->client->followRedirect();

        self::assertStatusCode(Response::HTTP_OK, $this->client);
        self::assertCount(2, $crawler->filter('tbody tr.official-report'));
        self::assertContains('CoPol de Paris (75)', $crawler->filter('tbody tr.official-report')->eq(1)->filter('td')->eq(0)->text());
        self::assertContains('10/10/2020 10:10', $crawler->filter('tbody tr.official-report')->eq(1)->filter('td')->eq(2)->text());
        self::assertContains('2', $crawler->filter('tbody tr.official-report')->eq(1)->filter('td')->eq(4)->text());
        self::assertContains('Referent Referent', $crawler->filter('tbody tr.official-report')->eq(1)->filter('td')->eq(5)->text());
    }

    public function provideAdherentsWithNoAccess(): iterable
    {
        yield ['benjyd@aol.com'];
        yield ['jacques.picard@en-marche.fr'];
    }

    public function provideAdherentsWithNoEditRight(): iterable
    {
        yield ['benjyd@aol.com'];
        yield ['jacques.picard@en-marche.fr'];
        yield ['referent-child@en-marche-dev.fr'];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
