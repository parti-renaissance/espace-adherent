<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadMissionTypeData;
use AppBundle\DataFixtures\ORM\LoadSummaryData;
use AppBundle\Entity\MemberSummary\Language;
use AppBundle\Form\SummaryType;
use AppBundle\Membership\ActivityPositions;
use AppBundle\Summary\Contract;
use AppBundle\Summary\Contribution;
use AppBundle\Summary\JobDuration;
use AppBundle\Summary\JobLocation;
use Symfony\Component\DomCrawler\Crawler;
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

    private const SECTION_HEADER = 'summary.header';
    private const SECTION_SYNTHESIS = 'summary.synthesis';
    private const SECTION_MISSIONS = 'summary.missions';
    private const SECTION_MOTIVATION = 'summary.motivation';
    private const SECTION_EXPERIENCES = 'summary.experiences';
    private const SECTION_RECENT_ACTIVITIES = 'summary.recent_activities';
    private const SECTION_SKILLS = 'summary.skills';
    private const SECTION_LANGUAGES = 'summary.languages';
    private const SECTION_TRAININGS = 'summary.trainings';
    private const SECTION_INTERESTS = 'summary.interests';
    private const SECTION_CONTACT = 'summary.contact';

    private const SECTIONS = [
        self::SECTION_HEADER,
        self::SECTION_SYNTHESIS,
        self::SECTION_MISSIONS,
        self::SECTION_MOTIVATION,
        self::SECTION_RECENT_ACTIVITIES,
        self::SECTION_EXPERIENCES,
        self::SECTION_SKILLS,
        self::SECTION_LANGUAGES,
        self::SECTION_TRAININGS,
        self::SECTION_INTERESTS,
        self::SECTION_CONTACT,
    ];

    public function provideActions()
    {
        yield 'Index' => ['/espace-adherent/mon-cv'];
        yield 'Handle experience' => ['/espace-adherent/mon-cv/experience'];
        yield 'Handle training' => ['/espace-adherent/mon-cv/formation'];
        yield 'Handle language' => ['/espace-adherent/mon-cv/langue'];

        foreach (SummaryType::STEPS as $step) {
            yield 'Handle step '.$step => ['/espace-adherent/mon-cv/'.$step];
        }
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

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateExperience()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(0, $crawler->filter('.summary-experience'));
        $this->assertSummaryCompletion(8, $crawler);
=======
        $this->assertCount(0, $crawler->filter('.cv__experience'));
>>>>>>> Fix tests

        $crawler = $this->client->click($crawler->filter('#summary-experiences .summary-add-item')->link());

        $company = 'Example';
        $position = 'TESTER';

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
        $this->assertSummaryCompletion(16, $crawler);
        $this->assertSame('L\'expérience a bien été sauvegardée.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(1, $experience = $crawler->filter('.cv__experience > div'));
        $this->assertContains($position, $experience->filter('h3')->text());
        $this->assertSame($company, $experience->filter('h4')->text());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateExperienceChangesOrder()
    {
        // This adherent has a summary and experiences already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

        $this->assertCount(2, $crawler->filter('.cv__experience'));
        $this->assertSummaryCompletion(100, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-experiences .summary-add-item')->link());

        $company = 'Example';

        $this->client->submit($crawler->filter('form[name=job_experience]')->form([
            'job_experience[company]' => $company,
            'job_experience[website]' => 'example.org',
            'job_experience[position]' => 'Tester',
            'job_experience[location]' => 'Somewhere over the rainbow',
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

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
<<<<<<< HEAD
        $this->assertCount(3, $experiences = $crawler->filter('.summary-experience'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(3, $experiences = $crawler->filter('.cv__experience'));
>>>>>>> Fix tests
        $this->assertSame($company, $experiences->eq(0)->filter('h4')->text());

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

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testEditExperienceChangesOrder()
    {
        // This adherent has a summary and experiences already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(2, $experiences = $crawler->filter('.summary-experience'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(2, $experiences = $crawler->filter('.cv__experience'));
>>>>>>> Fix tests

        $lastExperience = $experiences->eq(1);

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

<<<<<<< HEAD
        $this->assertCount(2, $crawler->filter('.summary-experience'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertSame('Univérsité Lyon 1', $crawler->filter('.summary-experience h4')->eq(0)->text());
=======
        $this->assertCount(2, $crawler->filter('.cv__experience'));
        $this->assertSame('Univérsité Lyon 1', $crawler->filter('.cv__experience h4')->eq(0)->text());
>>>>>>> Fix tests
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testDeleteExperienceChangesOrder()
    {
        // This adherent has a summary and experiences already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(2, $experiences = $crawler->filter('.summary-experience'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(2, $experiences = $crawler->filter('.cv__experience'));
>>>>>>> Fix tests

        $lastExperience = $experiences->eq(1);

        $this->assertSame('Univérsité Lyon 1', $lastExperience->filter('h4')->text());

        $this->client->submit($crawler->filter('.cv__experience')->eq(0)->selectButton('Supprimer')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
<<<<<<< HEAD
        $this->assertCount(1, $crawler->filter('.summary-experience'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(1, $crawler->filter('.cv__experience'));
>>>>>>> Fix tests

        $summary = $this->getSummaryRepository()->findOneForAdherent($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID));

        $this->assertCount(1, $experiences = $summary->getExperiences());

        $firstExperience = $experiences->first();

        $this->assertSame('Univérsité Lyon 1', $firstExperience->getCompany());
        $this->assertSame(1, $firstExperience->getDisplayOrder());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateTraining()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(0, $crawler->filter('.summary-training'));
        $this->assertSummaryCompletion(8, $crawler);
=======
        $this->assertCount(0, $crawler->filter('.cv__training'));
>>>>>>> Fix tests

        $crawler = $this->client->click($crawler->filter('#summary-trainings .summary-add-item')->link());

        $organization = 'EXAMPLE';
        $diploma = 'MASTER';
        $studyField = 'WEB DEVELOPMENT';

        $this->client->submit($crawler->filter('form[name=training]')->form([
            'training[organization]' => $organization,
            'training[diploma]' => $diploma,
            'training[study_field]' => $studyField,
            'training[started_at][month]' => '2',
            'training[started_at][year]' => '2012',
            'training[ended_at][month]' => '2',
            'training[ended_at][year]' => '2012',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSummaryCompletion(16, $crawler);
        $this->assertSame('La formation a bien été sauvegardée.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(1, $experience = $crawler->filter('.cv__training'));
        $this->assertSame($diploma.' - '.$studyField, $experience->filter('h3')->text());
        $this->assertSame(strtoupper($organization), $experience->filter('h4')->text());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateTrainingChangesOrder()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(2, $crawler->filter('.summary-training'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(2, $crawler->filter('.cv__training'));
>>>>>>> Fix tests

        $crawler = $this->client->click($crawler->filter('#summary-trainings .summary-add-item')->link());

        $diploma = 'MASTER';

        $this->client->submit($crawler->filter('form[name=training]')->form([
            'training[organization]' => 'Example',
            'training[diploma]' => $diploma,
            'training[study_field]' => 'Web development',
            'training[started_at][month]' => '2',
            'training[started_at][year]' => '2012',
            'training[ended_at][month]' => '2',
            'training[ended_at][year]' => '2012',
            'training[display_order][entry]' => '1',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
<<<<<<< HEAD
        $this->assertCount(3, $crawler->filter('.summary-training'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertContains($diploma, $crawler->filter('.summary-training h3')->eq(0)->text());
=======
        $this->assertCount(3, $crawler->filter('.cv__training'));
        $this->assertContains($diploma, $crawler->filter('.cv__training h3')->eq(0)->text());
>>>>>>> Fix tests

        $summary = $this->getSummaryRepository()->findOneForAdherent($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID));

        $this->assertCount(3, $trainings = $summary->getTrainings());

        foreach ($trainings as $training) {
            switch ($training->getDisplayOrder()) {
                case 1:
                    $this->assertSame($diploma, $training->getDiploma());
                    break;
                case 2:
                    $this->assertSame('DIPLÔME D\'INGÉNIEUR', $training->getDiploma());
                    break;
                case 3:
                    $this->assertSame('DUT GÉNIE BIOLOGIQUE', $training->getDiploma());
                    break;
            }
        }
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testEditTrainingChangesOrder()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(2, $trainings = $crawler->filter('.summary-training'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(2, $trainings = $crawler->filter('.cv__training'));
>>>>>>> Fix tests

        $lastTraining = $trainings->eq(1);

        $this->assertSame('DUT GÉNIE BIOLOGIQUE - BIO-INFORMATIQUE', $lastTraining->filter('h3')->text());

        $crawler = $this->client->click($lastTraining->selectLink('Modifier')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $newPosition = 1;

        $this->client->submit($crawler->filter('form[name=training]')->form([
            'training[display_order][entry]' => $newPosition,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

<<<<<<< HEAD
        $this->assertCount(2, $crawler->filter('.summary-training'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertSame('DUT Génie biologique - Bio-Informatique', $crawler->filter('.summary-training h3')->eq(0)->text());
=======
        $this->assertCount(2, $crawler->filter('.cv__training'));
        $this->assertSame('DUT GÉNIE BIOLOGIQUE - BIO-INFORMATIQUE', $crawler->filter('.cv__training h3')->eq(0)->text());
>>>>>>> Fix tests
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testDeleteTrainingChangesOrder()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(2, $trainings = $crawler->filter('.summary-training'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(2, $trainings = $crawler->filter('.cv__training'));
>>>>>>> Fix tests

        $lastTraining = $trainings->eq(1);

        $this->assertSame('DUT GÉNIE BIOLOGIQUE - BIO-INFORMATIQUE', $lastTraining->filter('h3')->text());

        $this->client->submit($crawler->filter('.cv__training')->eq(0)->selectButton('Supprimer')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
<<<<<<< HEAD
        $this->assertCount(1, $crawler->filter('.summary-training'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(1, $crawler->filter('.cv__training'));
>>>>>>> Fix tests

        $summary = $this->getSummaryRepository()->findOneForAdherent($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID));

        $this->assertCount(1, $trainings = $summary->getTrainings());

        $firstTraining = $trainings->first();

        $this->assertSame('DUT GÉNIE BIOLOGIQUE', $firstTraining->getDiploma());
        $this->assertSame(1, $firstTraining->getDisplayOrder());
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateLanguageWithoutSummary()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(0, $crawler->filter('.summary-language'));
        $this->assertSummaryCompletion(8, $crawler);
=======
        $this->assertCount(0, $crawler->filter('.cv__languages'));
>>>>>>> Fix tests

        $crawler = $this->client->click($crawler->filter('#summary-languages .summary-add-item')->link());

        $code = 'fr';
        $level = Language::LEVEL_FLUENT;

        $this->client->submit($crawler->filter('form[name=language]')->form([
            'language[code]' => $code,
            'language[level]' => $level,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('La langue a bien été sauvegardée.', $crawler->filter('.flash__inner')->text());
<<<<<<< HEAD
        $this->assertCount(1, $language = $crawler->filter('.summary-language'));
        $this->assertSummaryCompletion(16, $crawler);
        $this->assertSame('Français - '.ucfirst($level), $language->filter('p')->text());
=======
        $this->assertCount(1, $language = $crawler->filter('.cv__languages'));
        $this->assertSame('Français - '.ucfirst($level), $language->filter('.cv__languages > div')->text());
>>>>>>> Fix tests
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testCreateLanguageWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(3, $crawler->filter('.summary-language'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(3, $crawler->filter('.cv__languages'));
>>>>>>> Fix tests

        $crawler = $this->client->click($crawler->filter('#summary-languages .summary-add-item')->link());

        $code = 'fr';
        $level = Language::LEVEL_FLUENT;

        $this->client->submit($crawler->filter('form[name=language]')->form([
            'language[code]' => $code,
            'language[level]' => $level,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

<<<<<<< HEAD
        $this->assertCount(4, $crawler->filter('.summary-language'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(4, $crawler->filter('.cv__languages'));
>>>>>>> Fix tests
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testEditLanguage()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertSummaryCompletion(100, $crawler);

        $firstLanguage = $crawler->filter('.summary-language')->eq(2);
=======
        $firstLanguage = $crawler->filter('.cv__languages')->eq(2);
>>>>>>> Fix tests

        $this->assertSame('Espagnol - Bonne maîtrise', $firstLanguage->filter('.cv__languages > div')->text());

        $crawler = $this->client->click($firstLanguage->selectLink('Modifier')->link());

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $newLevel = Language::LEVEL_HIGH;

        $this->client->submit($crawler->filter('form[name=language]')->form([
            'language[level]' => $newLevel,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

<<<<<<< HEAD
        $this->assertCount(3, $crawler->filter('.summary-language'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertSame('Espagnol - Maîtrise parfaite', $crawler->filter('.summary-language p')->eq(2)->text());
=======
        $this->assertCount(3, $crawler->filter('.cv__languages'));
        $this->assertSame('Espagnol - Maîtrise parfaite', $crawler->filter('.cv__languages > div')->eq(2)->text());
>>>>>>> Fix tests
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testDeleteLanguage()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

<<<<<<< HEAD
        $this->assertCount(3, $languages = $crawler->filter('.summary-language'));
        $this->assertSummaryCompletion(100, $crawler);
=======
        $this->assertCount(3, $languages = $crawler->filter('.cv__languages'));
>>>>>>> Fix tests

        $firstLanguage = $languages->eq(0);

        $this->assertSame('Français - Langue maternelle', $firstLanguage->filter('.cv__languages > div')->text());

        $this->client->submit($firstLanguage->selectButton('Supprimer')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
<<<<<<< HEAD
        $this->assertCount(2, $crawler->filter('.summary-language'));
        $this->assertSummaryCompletion(100, $crawler);
        $this->assertSame('Anglais - Maîtrise parfaite', $crawler->filter('.summary-language p')->eq(0)->text());
=======
        $this->assertCount(2, $crawler->filter('.cv__languages'));
        $this->assertSame('Anglais - Maîtrise parfaite', $crawler->filter('.cv__languages > div')->eq(0)->text());
>>>>>>> Fix tests
    }

    public function testSearchSkillUserHasNot()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        // Search the skill that user has not, should find one skill
        $this->client->request(Request::METHOD_GET, 'espace-adherent/mon-cv/competences/autocompletion?term=outi');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        $this->assertNotEmpty(\GuzzleHttp\json_decode($content, true));
        $skills = \GuzzleHttp\json_decode($content, true);
        $this->assertSame(1, count($skills));
        foreach ($skills as $skill) {
            $this->assertSame('Outils médias', $skill);
        }
    }

    public function testSearchSkillUserHas()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        // Search the skill that user already has, nothing should be found
        $this->client->request(Request::METHOD_GET, 'espace-adherent/mon-cv/competences/autocompletion?term=sof');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        $this->assertEmpty(\GuzzleHttp\json_decode($content, true));
        $this->client->followRedirects(true);
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testAddSkillWithoutSummary()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

        $this->assertCount(0, $crawler->filter('.summary-skill'));
        $this->assertSummaryCompletion(8, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-skills .summary-modify')->link());

        $skill1 = 'Développement';
        $skill2 = 'Gestion des bases';

        $skillCollection = $crawler->filter('#summary_skills')->getNode(0);
        $this->appendCollectionFormPrototype($skillCollection, '0');
        $this->appendCollectionFormPrototype($skillCollection, '1');

        $this->client->submit($crawler->filter('form[name=summary]')->form(), [
            'summary[skills][0][name]' => $skill1,
            'summary[skills][1][name]' => $skill2,
        ]);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(2, $skills = $crawler->filter('.summary-skill'));
        $this->assertSame($skill1, $skills->eq(0)->filter('p')->text());
        $this->assertSame($skill2, $skills->eq(1)->filter('p')->text());
        $this->assertSummaryCompletion(16, $crawler);
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testModifySkillsWithSummary()
    {
        // This adherent has a summary and skills already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv');

        $this->assertCount(4, $crawler->filter('.summary-skill'));
        $this->assertSummaryCompletion(100, $crawler);

        $crawler = $this->client->click($crawler->filter('#summary-skills .summary-modify')->link());

        $skill1 = 'Développement';
        $skill2 = 'Gestion des bases';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[skills][1][name]' => $skill1,
            'summary[skills][2][name]' => $skill2,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertCount(4, $skills = $crawler->filter('.summary-skill'));
        $this->assertSame($skill1, $skills->eq(1)->filter('p')->text());
        $this->assertSame($skill2, $skills->eq(2)->filter('p')->text());
        $this->assertSummaryCompletion(100, $crawler);
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepSynthesisWithoutSummary()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/synthesis');

        $this->assertCount(10, $crawler->filter('form[name=summary] input'));
        $this->assertCount(1, $crawler->filter('form[name=summary] select'));
        $this->assertCount(1, $crawler->filter('form[name=summary] textarea'));

        $profession = 'Professeur';
        $synopsis = 'This should be a professional synopsis.';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[current_profession]' => $profession,
            'summary[current_position]' => ActivityPositions::UNEMPLOYED,
            'summary[contribution_wish]' => Contribution::VOLUNTEER,
            'summary[availabilities]' => [JobDuration::PART_TIME],
            'summary[job_locations][0]' => JobLocation::ON_SITE,
            'summary[job_locations][1]' => JobLocation::ON_REMOTE,
            'summary[professional_synopsis]' => $synopsis,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(39, $crawler);

        $synthesis = $this->getSummarySection($crawler, self::SECTION_SYNTHESIS);

        $this->assertSame($profession, $synthesis->filter('h2')->text());
        $this->assertSame('En recherche d\'emploi', $synthesis->filter('h3')->text());
        $this->assertCount(1, $synthesis->filter('p:contains("Missions de bénévolat")'));
        $this->assertCount(1, $synthesis->filter('p:contains("Temps partiel")'));
        $this->assertCount(1, $synthesis->filter('p:contains("Sur site ou à distance")'));
        $this->assertCount(1, $synthesis->filter(sprintf('p:contains("%s")', $synopsis)));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepSynthesisWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/synthesis');

        $this->assertCount(10, $crawler->filter('form[name=summary] input'));
        $this->assertCount(1, $crawler->filter('form[name=summary] select'));
        $this->assertCount(1, $crawler->filter('form[name=summary] textarea'));

        $profession = 'Professeur';
        $synopsis = 'This should be a professional synopsis.';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[current_profession]' => $profession,
            'summary[current_position]' => ActivityPositions::UNEMPLOYED,
            'summary[contribution_wish]' => Contribution::VOLUNTEER,
            'summary[availabilities]' => [JobDuration::PART_TIME],
            'summary[job_locations][1]' => JobLocation::ON_REMOTE,
            'summary[professional_synopsis]' => $synopsis,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $synthesis = $this->getSummarySection($crawler, self::SECTION_SYNTHESIS);

        $this->assertSame($profession, $synthesis->filter('h2')->text());
        $this->assertSame('En recherche d\'emploi', $synthesis->filter('h3')->text());
        $this->assertCount(1, $synthesis->filter('p:contains("Missions de bénévolat")'));
        $this->assertCount(1, $synthesis->filter('p:contains("Temps partiel")'));
        $this->assertCount(1, $synthesis->filter('p:contains("À distance")'));
        $this->assertCount(1, $synthesis->filter(sprintf('p:contains("%s")', $synopsis)));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepMissionsWithoutSummary()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/missions');

        $this->assertCount(7, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[mission_type_wishes][0]' => '1',
            'summary[mission_type_wishes][2]' => '3',
            'summary[mission_type_wishes][4]' => '5',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(16, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_MISSIONS);

        $this->assertCount(3, $missions->filter('.summary-wish'));
        $this->assertSame('MISSIONS DE BÉNÉVOLAT', trim($missions->filter('.summary-wish')->eq(0)->text()));
        $this->assertSame('ACTION PUBLIQUE', trim($missions->filter('.summary-wish')->eq(1)->text()));
        $this->assertSame('ECONOMIE', trim($missions->filter('.summary-wish')->eq(2)->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepMissionsWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/missions');

        $this->assertCount(7, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[mission_type_wishes][1]' => '2',
            'summary[mission_type_wishes][3]' => '4',
            'summary[mission_type_wishes][5]' => '6',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_MISSIONS);

        $this->assertCount(4, $missions->filter('.summary-wish'));
        $this->assertSame('MISSIONS DE BÉNÉVOLAT', trim($missions->filter('.summary-wish')->eq(0)->text()));
        $this->assertSame('MISSION LOCALE', trim($missions->filter('.summary-wish')->eq(1)->text()));
        $this->assertSame('ENGAGEMENT', trim($missions->filter('.summary-wish')->eq(2)->text()));
        $this->assertSame('EMPLOI', trim($missions->filter('.summary-wish')->eq(3)->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepMotivationWithoutSummary()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/motivation');

        $this->assertCount(1, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(1, $crawler->filter('form[name=summary] textarea'));

        $motivation = 'I\'m motivated.';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[motivation]' => $motivation,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(16, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_MOTIVATION);

        $this->assertCount(1, $missions->filter('p'));
        $this->assertSame($motivation, trim($missions->filter('p')->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepMotivationWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/motivation');

        $this->assertCount(1, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(1, $crawler->filter('form[name=summary] textarea'));

        $motivation = 'I\'m motivated.';

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[motivation]' => $motivation,
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_MOTIVATION);

        $this->assertCount(1, $missions->filter('p'));
        $this->assertSame($motivation, trim($missions->filter('p')->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepInterestsWithoutSummary()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/interests');

        $this->assertCount(19, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[member_interests][0]' => 'agriculture',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(16, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_INTERESTS);

        $this->assertCount(1, $missions->filter('p'));
        $this->assertSame('Agriculture', trim($missions->filter('p')->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepInterestsWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/interests');

        $this->assertCount(19, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[member_interests][4]' => 'egalite',
            'summary[member_interests][10]' => 'jeunesse',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_INTERESTS);

        $this->assertCount(2, $missions->filter('p'));
        $this->assertSame('Jeunesse', trim($missions->filter('p')->eq(0)->text()));
        $this->assertSame('Egalité F / H', trim($missions->filter('p')->eq(1)->text()));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepContactWithoutSummary()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/contact');

        $this->assertCount(7, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[contact_email]' => 'toto@example.org',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount(++$summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(16, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_CONTACT);

        $this->assertCount(1, $missions->filter('.summary-contact-email'));
        $this->assertCount(0, $missions->filter('.summary-contact-facebook'));
        $this->assertCount(0, $missions->filter('.summary-contact-linked_in'));
        $this->assertCount(0, $missions->filter('.summary-contact-twitter'));
    }

    /**
     * @depends testActionsAreSuccessfulAsAdherentWithoutSummary
     */
    public function testStepContactWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/contact');

        $this->assertCount(7, $crawler->filter('form[name=summary] input'));
        $this->assertCount(0, $crawler->filter('form[name=summary] select'));
        $this->assertCount(0, $crawler->filter('form[name=summary] textarea'));

        $this->client->submit($crawler->filter('form[name=summary]')->form([
            'summary[contact_email]' => 'toto@example.org',
            'summary[linked_in_url]' => 'https://linkedin.com/in/lucieoliverafake',
            'summary[website_url]' => 'https://lucieoliverafake.com',
            'summary[facebook_url]' => 'https://facebook.com/lucieoliverafake',
            'summary[twitter_nickname]' => 'lucieoliverafake',
            'summary[viadeo_url]' => 'http://fr.viadeo.com/fr/profile/lucie.olivera.fake',
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Vos modifications ont bien été enregistrées.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $missions = $this->getSummarySection($crawler, self::SECTION_CONTACT);

        $this->assertCount(1, $missions->filter('.summary-contact-email'));
        $this->assertCount(1, $missions->filter('.summary-contact-facebook'));
        $this->assertCount(1, $missions->filter('.summary-contact-linked_in'));
        $this->assertCount(1, $missions->filter('.summary-contact-twitter'));
        $this->assertCount(1, $missions->filter('.summary-contact-twitter'));
    }

    public function testPublishActionWithoutSummary()
    {
        $summariesCount = count($this->getSummaryRepository()->findAll());

        // This adherent has no summary yet
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/publier');

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertCount($summariesCount, $this->getSummaryRepository()->findAll());
        $this->assertSame('Merci de compléter votre CV avant de le publier.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(8, $crawler);
    }

    public function testPublishingProcessWithSummary()
    {
        // This adherent has a summary and trainings already
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-cv/depublier');

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-cv', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame('Votre CV a bien été dépublié.', $crawler->filter('.flash__inner')->text());
        $this->assertSummaryCompletion(100, $crawler);

        $this->client->click($crawler->selectLink('publier mon CV')->link());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertClientIsRedirectedTo('/membre/lucie-olivera', $this->client);

        $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadMissionTypeData::class,
            LoadSummaryData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }

    private function getSummarySection(Crawler $crawler, string $section): Crawler
    {
        return $crawler->filter('.adherent_summary section')->eq(array_search($section, self::SECTIONS));
    }

    private function assertSummaryCompletion(int $completion, Crawler $crawler)
    {
        $this->assertSame($completion.'%  profile complété', trim($crawler->filter('.summary-completion')->text()));
    }
}
