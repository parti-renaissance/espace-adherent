<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadCommitteeEventData;
use App\DataFixtures\ORM\LoadCommitteeV1Data;
use App\Entity\Report\CommitteeReport;
use App\Entity\Report\CommunityEventReport;
use App\Report\ReportType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('controller')]
class ReportControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public static function provideReportableSubject(): iterable
    {
        yield 'Committee' => [
            CommitteeReport::class,
            '/comites/en-marche-paris-8',
            LoadCommitteeV1Data::COMMITTEE_1_UUID,
        ];
        yield 'Event' => [
            CommunityEventReport::class,
            \sprintf('/evenements/%s-reunion-de-reflexion-marseillaise', (new \DateTime('2018-05-18'))->modify('+17 days')->format('Y-m-d')),
            LoadCommitteeEventData::EVENT_5_UUID,
        ];
    }

    #[DataProvider('provideReportableSubject')]
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

    private function assertReportUri(Crawler $crawler, $reportClass, $subjectUuid, $subjectUrl): void
    {
        $reportUri = \sprintf(
            'http://%s/report/%s/%s?redirectUrl=%s',
            $this->getParameter('app_host'),
            $this->getUriTypeFromReportCLass($reportClass),
            $subjectUuid,
            $subjectUrl
        );

        $this->assertSame($reportUri, $crawler->getUri());
    }

    private function getUriTypeFromReportCLass($reportClass): string
    {
        $this->assertContains($reportClass, ReportType::LIST);

        $type = array_search($reportClass, ReportType::LIST, true);

        $this->assertContains($type, ReportType::URI_MAP);

        return array_search($type, ReportType::URI_MAP, true);
    }
}
