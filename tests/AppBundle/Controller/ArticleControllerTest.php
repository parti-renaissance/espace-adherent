<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadArticleData;
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

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $response->headers->get('cache-control'));
    }

    public function testArticleWithoutImage()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/article/sans-image');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertNotContains('<img src="/assets/images/article.jpg', $this->client->getResponse()->getContent());

        // Assert Cloudflare will store this page in cache
        $this->assertContains('public, s-maxage=', $response->headers->get('cache-control'));
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
            ['/actualites'],
            ['/actualites/videos'],
            ['/actualites/discours'],
            ['/actualites/medias'],
            ['/actualites/communiques'],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
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
