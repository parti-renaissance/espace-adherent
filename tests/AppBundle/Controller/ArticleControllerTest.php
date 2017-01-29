<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadArticleData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadLiveLinkData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testArticlePublished()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/article/outre-mer');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertContains('<img src="/assets/images/article.jpg', $this->client->getResponse()->getContent());
    }

    public function testArticleWithoutImage()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/article/sans-image');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertNotContains('<img src="/assets/images/article.jpg', $this->client->getResponse()->getContent());
    }

    public function testArticleDraft()
    {
        $this->client->request(Request::METHOD_GET, '/article/brouillon');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    /**
     * @dataProvider provideHub
     */
    public function testHub($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
    }

    public function provideHub()
    {
        return [
            ['/articles/actualites'],
            ['/articles/actualites/2'],
            ['/articles/videos'],
            ['/articles/videos/2'],
            ['/articles/discours'],
            ['/articles/discours/2'],
            ['/articles/medias'],
            ['/articles/medias/2'],
            ['/articles/communiques'],
            ['/articles/communiques/2'],
        ];
    }

    public function testScenarioHomeHub()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->filter('a:contains("Actualités")')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->click($crawler->filter('a:contains("Communiqués")')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->back();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadHomeBlockData::class,
            LoadLiveLinkData::class,
            LoadArticleData::class,
        ]);

        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->client = null;

        parent::tearDown();
    }
}
