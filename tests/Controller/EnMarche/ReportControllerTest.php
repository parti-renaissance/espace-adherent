<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenActionData;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\Report\CitizenActionReport;
use AppBundle\Entity\Report\CitizenProjectReport;
use AppBundle\Entity\Report\CommitteeReport;
use AppBundle\Entity\Report\CommunityEventReport;
use AppBundle\Report\ReportType;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group controller
 */
class ReportControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }

    public function provideReportableSubject(): iterable
    {
        yield 'Citizen action' => [
            CitizenActionReport::class,
            sprintf('/action-citoyenne/%s-projet-citoyen-de-zurich', \date('Y-m-d', strtotime('+3 days'))),
            LoadCitizenActionData::CITIZEN_ACTION_1_UUID,
        ];
        yield 'Citizen project' => [
            CitizenProjectReport::class,
            '/projets-citoyens/75008-le-projet-citoyen-a-paris-8',
            LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID,
        ];
        yield 'Committee' => [
            CommitteeReport::class,
            '/comites/en-marche-paris-8',
            LoadAdherentData::COMMITTEE_1_UUID,
        ];
        yield 'Event' => [
            CommunityEventReport::class,
            sprintf('/evenements/%s-reunion-de-reflexion-marseillaise', \date('Y-m-d', strtotime('+17 days'))),
            LoadEventData::EVENT_5_UUID,
        ];
    }

    /**
     * @dataProvider provideReportableSubject
     */
    public function testAdherentCanReportSubject($reportClass, $subjectUrl, $subjectUuid): void
    {
        $reportRepository = $this->getRepository($reportClass);
        $initialReportCount = \count($reportRepository->findAll());

        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $crawler = $this->client->request(Request::METHOD_GET, $subjectUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Signaler')->link());

        $this->assertReportUri($crawler, $reportClass, $subjectUuid, $subjectUrl);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Envoyer mon signalement')->form([
            'report_command' => [
                'reasons' => [3 => 'other'],
                'comment' => 'Ce sujet n\'est pas conforme',
            ],
        ]));

        $this->assertClientIsRedirectedTo($subjectUrl, $this->client);
        $this->assertSame($initialReportCount + 1, \count($reportRepository->findAll()));
    }

    public function testAdherentIsRedirectedToWebRootIfRedirectUrlIsNotAValidInternalPath(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/report/projets-citoyens/aa364092-3999-4102-930c-f711ef971195?redirectUrl=http%3A%2F%2Fje-te-hack.com');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Envoyer mon signalement')->form([
            'report_command' => [
                'reasons' => [0 => 'intellectual_property', 1 => 'illicit_content'],
            ],
        ]));
        $this->assertClientIsRedirectedTo('/', $this->client);
    }

    public function testAdherentIsRedirectedWebRootIfNoRedirectUrlIsProvided(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $crawler = $this->client->request(Request::METHOD_GET, '/report/projets-citoyens/aa364092-3999-4102-930c-f711ef971195');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Envoyer mon signalement')->form([
            'report_command' => [
                'reasons' => [0 => 'intellectual_property'],
            ],
        ]));
        $this->assertClientIsRedirectedTo('/', $this->client);
    }

    private function assertReportUri(Crawler $crawler, $reportClass, $subjectUuid, $subjectUrl): void
    {
        $reportUri = \sprintf(
            'http://%s/report/%s/%s?redirectUrl=%s',
            $this->hosts['app'],
            $this->getUriTypeFromReportCLass($reportClass),
            $subjectUuid,
            $subjectUrl
        );

        $this->assertSame($reportUri, $crawler->getUri());
    }

    private function getUriTypeFromReportCLass($reportClass): string
    {
        $this->assertContains($reportClass, ReportType::LIST);

        $type = \array_search($reportClass, ReportType::LIST, true);

        $this->assertContains($type, ReportType::URI_MAP);

        return \array_search($type, ReportType::URI_MAP, true);
    }
}
