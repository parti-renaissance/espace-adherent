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
        $crawler = $this->client->request(Request::METHOD_GET, '/membre/lucie-olivera');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->assertSame(1, $crawler->filter('.summary-contact-email')->count());
        $this->assertSame(2, $crawler->filter('.summary-experience h3')->count());
        $this->assertSame('CDI Bio-informaticien', $crawler->filter('.summary-experience h3')->eq(0)->text());
        $this->assertSame('Institut KNURE', $crawler->filter('.summary-experience h4')->eq(0)->text());
        $this->assertSame('CDI Professeur', $crawler->filter('.summary-experience h3')->eq(1)->text());
        $this->assertSame('Univérsité Lyon 1', $crawler->filter('.summary-experience h4')->eq(1)->text());
        $this->assertSame(3, $crawler->filter('.summary-language p')->count());
        $this->assertSame(1, $crawler->filter('.summary-language p:contains("Français - Langue maternelle")')->count());
        $this->assertSame(1, $crawler->filter('.summary-language p:contains("Anglais - Maîtrise parfaite")')->count());
        $this->assertSame(1, $crawler->filter('.summary-language p:contains("Espagnol - Bonne maîtrise")')->count());
        $this->assertSame(4, $crawler->filter('.summary-skill p')->count());
        $this->assertSame(1, $crawler->filter('.summary-skill p:contains("Software")')->count());
        $this->assertSame(1, $crawler->filter('.summary-skill p:contains("Analyze")')->count());
        $this->assertSame(1, $crawler->filter('.summary-skill p:contains("Mathématiques")')->count());
        $this->assertSame(1, $crawler->filter('.summary-skill p:contains("Statistique")')->count());
        $this->assertSame(4, $crawler->filter('.summary-training p')->count());
        $this->assertSame(1, $crawler->filter('.summary-training h3:contains("Diplôme d\'ingénieur - Bio-Informatique")')->count());
        $this->assertSame(1, $crawler->filter('.summary-training h3:contains("DUT Génie biologique - Bio-Informatique")')->count());
        $this->assertSame(1, $crawler->filter('.summary-training p:contains("Master en Bio-Informatique")')->count());
        $this->assertSame(1, $crawler->filter('.summary-training p:contains("Génie biologique option Bio-Informatique")')->count());
        $this->assertSame('mailto:luciole1989@spambox.fr', $crawler->filter('.summary-contact-email a')->attr('href'));
        $this->assertSame('https://www.facebook.com/lucie-olivera-fake', $crawler->filter('.summary-contact-facebook a')->attr('href'));
        $this->assertSame('https://twitter.com/lucie-olivera-fake', $crawler->filter('.summary-contact-twitter a')->attr('href'));

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
