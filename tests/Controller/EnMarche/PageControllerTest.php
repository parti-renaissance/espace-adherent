<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadFacebookVideoData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadPageData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class PageControllerTest extends SqliteWebTestCase
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

        $this->assertClientIsRedirectedTo('https://legislatives.en-marche.fr', $this->client);
    }

    public function providePages()
    {
        yield ['/emmanuel-macron'];
        yield ['/emmanuel-macron/revolution'];
        yield ['/emmanuel-macron/videos'];
        yield ['/le-mouvement'];
        yield ['/le-mouvement/notre-organisation'];
        yield ['/le-mouvement/les-comites'];
        yield ['/le-mouvement/devenez-benevole'];
        yield ['/okcandidatlegislatives'];
        yield ['/mentions-legales'];
        yield ['/bot'];
        yield ['/elles-marchent'];
        yield ['/campus/mooc'];
        yield ['/campus'];
        yield ['/campus/mooc'];
        yield ['/campus/dificultes-internet'];
        yield ['/action-talents'];
        yield ['/action-talents/candidater'];
        yield ['/nos-offres'];
        yield ['/listes-bureau-executif'];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadFacebookVideoData::class,
            LoadHomeBlockData::class,
            LoadPageData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
