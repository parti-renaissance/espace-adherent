<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadSummaryData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class SummaryControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    public function testAccessAndDisplaySummaryPage()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/member/michelle-dufour');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame(1, $crawler->filter('.summary-contact-email')->count());
        $this->assertSame(2, $crawler->filter('#summary_experiences h3')->count());
        $this->assertSame(1, $crawler->filter('#summary_experiences h3:contains("Bio-informaticien")')->count());
        $this->assertSame(1, $crawler->filter('#summary_experiences h4:contains("INSTITUT KNURE")')->count());
        $this->assertSame(1, $crawler->filter('#summary_experiences h3:contains("Professeur")')->count());
        $this->assertSame(1, $crawler->filter('#summary_experiences h4:contains("UNIVÉRSITÉ LYON 1")')->count());
        $this->assertSame(3, $crawler->filter('#summary_languages p')->count());
        $this->assertSame(1, $crawler->filter('#summary_languages p:contains("Français - Langue maternelle")')->count());
        $this->assertSame(1, $crawler->filter('#summary_languages p:contains("Anglais - Maîtrise parfaite")')->count());
        $this->assertSame(1, $crawler->filter('#summary_languages p:contains("Espagnol - Bonne maîtrise")')->count());
        $this->assertSame(4, $crawler->filter('#summary_skills p')->count());
        $this->assertSame(1, $crawler->filter('#summary_skills p:contains("Software")')->count());
        $this->assertSame(1, $crawler->filter('#summary_skills p:contains("Analyze")')->count());
        $this->assertSame(1, $crawler->filter('#summary_skills p:contains("Mathématiques")')->count());
        $this->assertSame(1, $crawler->filter('#summary_skills p:contains("Statistique")')->count());
        $this->assertSame(2, $crawler->filter('#summary_training p')->count());
        $this->assertSame(1, $crawler->filter('#summary_training h3:contains("Diplôme d\'ingénieur - Bio-Informatique")')->count());
        $this->assertSame(1, $crawler->filter('#summary_training h3:contains("DUT Génie biologique - Bio-Informatique")')->count());
        $this->assertSame(1, $crawler->filter('#summary_training p:contains("Master en Bio-Informatique")')->count());
        $this->assertSame(1, $crawler->filter('#summary_training p:contains("Génie biologique option Bio-Informatique")')->count());
        $this->assertSame('mailto:michelle.dufour.2@example.ch', $crawler->filter('.summary-contact-email a')->attr('href'));
        $this->assertSame('https://www.facebook.com/michelle-dufour-fake', $crawler->filter('.summary-contact-facebook a')->attr('href'));
        $this->assertSame('https://twitter.com/michelle-dufour-fake', $crawler->filter('.summary-contact-twitter a')->attr('href'));

        $this->assertSame(0, $crawler->filter('.button:contains("Modifer")')->count());
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
