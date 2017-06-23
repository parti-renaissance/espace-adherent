<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadSummaryData;
use AppBundle\Summary\Contract;
use AppBundle\Summary\JobDuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class SummaryManagerControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    public function provideActions()
    {
        yield 'Index' => ['/espace-adherent/mon-cv'];
        yield 'Handle experience' => ['/espace-adherent/mon-cv/experience'];
    }

    /**
     * @dataProvider provideActions
     */
    public function testActionsAreForbiddenAsAnonymous(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('http://localhost/espace-adherent/connexion', $this->client);
    }

    /**
     * @dataProvider provideActions
     */
    public function testActionsAreSuccessfulAsAdherentWithSummary(string $path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    /**
     * @dataProvider provideActions
     */
    public function testActionsAreSuccessfulAsAdherentWithoutSummary(string $path)
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testCreateExperience()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/experience');

        $company = 'Example';
        $position = 'Tester';

        $this->client->submit($crawler->filter('form[name=job_experience]')->form([
            'job_experience[company]' => $company,
            'job_experience[website]' => 'example.org',
            'job_experience[position]' => $position,
            'job_experience[location]' => 'Somewhere over the rainbow',
            'job_experience[started_at][month]' => '2',
            'job_experience[started_at][year]' => '2012',
            'job_experience[ended_at][month]' => '2',
            'job_experience[ended_at][year]' => '2012',
            'job_experience[contract]' => Contract::TEMPORARY,
            'job_experience[duration]' => JobDuration::FULL_TIME,
            'job_experience[description]' => 'Lorem ipsum',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('L\'expérience a bien été sauvegardée.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(1, $experience = $crawler->filter('.summary-experience'));
        $this->assertContains($position, $experience->filter('h3')->text());
        $this->assertSame($company, $experience->filter('h4')->text());
    }

    public function testCreateExperienceChangesOrder()
    {
        // This adherent has a summary and experiences already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/experience');

        $company = 'Example';
        $website = 'example.org';
        $position = 'Tester';
        $location = 'Somewhere over the rainbow';

        $this->client->submit($crawler->filter('form[name=job_experience]')->form([
            'job_experience[company]' => $company,
            'job_experience[website]' => $website,
            'job_experience[position]' => $position,
            'job_experience[location]' => $location,
            'job_experience[started_at][month]' => '2',
            'job_experience[started_at][year]' => '2012',
            'job_experience[ended_at][month]' => '2',
            'job_experience[ended_at][year]' => '2012',
            'job_experience[contract]' => Contract::TEMPORARY,
            'job_experience[duration]' => JobDuration::FULL_TIME,
            'job_experience[description]' => 'Lorem ipsum',
            'job_experience[display_order][entry]' => '1',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $summary = $this->getSummaryRepository()->findOneForAdherent($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID));

        $this->assertCount(3, $experiences = $summary->getExperiences());

        foreach ($experiences as $experience) {
            switch ($experience->getDisplayOrder()) {
                case 1:
                    $this->assertSame('Example', $experience->getCompany());
                    break;
                case 2:
                    $this->assertSame('Institut KNURE', $experience->getCompany());
                    break;
                case 3:
                    $this->assertSame('Univérsité Lyon 1', $experience->getCompany());
                    break;
            }
        }
    }

    public function testEditExperienceChangesOrder()
    {
        // This adherent has a summary and experiences already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $lastExperience = $crawler->filter('.summary-experience')->eq(1);

        $this->assertSame('Univérsité Lyon 1', $lastExperience->filter('h4')->text());

        $crawler = $this->client->click($lastExperience->selectLink('Modifier')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $newPosition = 1;

        $this->client->submit($crawler->filter('form[name=job_experience]')->form([
            'job_experience[display_order][entry]' => $newPosition,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertSame('Univérsité Lyon 1', $crawler->filter('.summary-experience')->eq(0)->filter('h4')->text());
    }

    public function testDeleteExperienceChangesOrder()
    {
        // This adherent has a summary and experiences already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $lastExperience = $crawler->filter('.summary-experience')->eq(1);

        $this->assertSame('Univérsité Lyon 1', $lastExperience->filter('h4')->text());

        $this->client->submit($crawler->filter('.summary-experience')->eq(0)->selectButton('Supprimer')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $summary = $this->getSummaryRepository()->findOneForAdherent($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID));

        $this->assertCount(1, $experiences = $summary->getExperiences());

        $firstExperience = $experiences->first();

        $this->assertSame('Univérsité Lyon 1', $firstExperience->getCompany());
        $this->assertSame(1, $firstExperience->getDisplayOrder());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadSummaryData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
