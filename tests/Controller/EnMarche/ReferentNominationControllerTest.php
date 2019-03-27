<?php

namespace Tests\AppBundle\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group controller
 */
class ReferentNominationControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testOurReferentsDirectory()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/le-mouvement/nos-referents');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $referents = $crawler->filter('.legislatives_candidate');

        // Check the order of candidates
        $this->assertSame(3, $referents->count());
        $this->assertSame('Referent75and77 Referent75and77', $referents->first()->filter('h1')->text());
        $this->assertSame('Referent child Referent child', $referents->eq(2)->filter('h1')->text());
        $this->assertSame('Referent Referent', $referents->eq(1)->filter('h1')->text());

        $crawler = $this->client->click($crawler->selectLink('Referent75and77 Referent75and77')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        // Check profile information of the first candidate
        $profile = $crawler->filter('#candidate-profile');
        $description = $crawler->filter('#candidate-description');
        $links = $profile->filter('a');

        $this->assertSame(1, $profile->filter('#candidat-profile-picture')->count());
        $this->assertSame('Referent75and77 Referent75and77', $profile->filter('h1')->text());
        $this->assertSame('Paris', $profile->filter('#candidate-district-name')->text());
        $this->assertSame('https://twitter.com/ref75', $links->first()->attr('href'));
        $this->assertSame('https://www.facebook.com/ref75', $links->eq(1)->attr('href'));
        $this->assertSame('https://www.linkedin.com/ref75', $links->eq(2)->attr('href'));
        $this->assertSame(4, $description->filter('p')->count());

        $this->client->click($crawler->selectLink('Retour à la liste des référents')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
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
