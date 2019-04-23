<?php

namespace Tests\AppBundle\Controller\EnMarche;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class PageControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider providePages
     */
    public function testPages(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
    }

    /**
     * @depends testPages
     */
    public function testEmmanuelMacronVideos()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/emmanuel-macron/videos');

        $this->assertSame(12, $crawler->filter('.fb-video')->count());

        $videoGridText = $crawler->filter('.videos__grid')->text();

        $this->assertContains('Laurence Haïm a un message pour vous. Inscrivez-vous ➜ en-marche.fr/bercy', $videoGridText);
        $this->assertContains('#MacronPau avec les helpers en coulisses. Allez allez ! Cette révolution nous allons la porter.', $videoGridText);
        $this->assertNotContains('Découvrez le teaser', $videoGridText);
    }

    public function testMouvementLegislativesAction()
    {
        $this->client->request(Request::METHOD_GET, '/le-mouvement/legislatives');

        $this->assertClientIsRedirectedTo('https://legislatives.en-marche.fr', $this->client, false, true);
    }

    public function providePages()
    {
        yield ['/emmanuel-macron'];
        yield ['/emmanuel-macron/revolution'];
        yield ['/emmanuel-macron/videos'];
        yield ['/le-mouvement'];
        yield ['/le-mouvement/les-comites'];
        yield ['/le-mouvement/devenez-benevole'];
        yield ['/okcandidatlegislatives'];
        yield ['/mentions-legales'];
        yield ['/elles-marchent'];
        yield ['/formation'];
        yield ['/formation/dificultes-internet'];
        yield ['/cestduconcret'];
        yield ['/action-talents'];
        yield ['/action-talents/candidater'];
        yield ['/nos-offres'];
        yield ['/candidatures-delegue-general-et-bureau-executif'];
        yield ['/emmanuel-macron/test'];
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
