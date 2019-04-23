<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Controller\EnMarche\ArticleController;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group article
 */
class ArticleControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testRedirectArticlePublished()
    {
        $this->client->request(Request::METHOD_GET, '/article/outre-mer');

        $this->assertClientIsRedirectedTo('/articles/actualites/outre-mer', $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }

    public function testArticlePublished()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/articles/actualites/outre-mer');

        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertContains('<img src="/assets/images/article.jpg', $this->client->getResponse()->getContent());
    }

    public function testArticleWithoutImage()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/articles/discours/sans-image');

        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertNotContains('<img src="/assets/images/article.jpg', $this->client->getResponse()->getContent());
    }

    public function testArticleDraft()
    {
        $this->client->request(Request::METHOD_GET, '/articles/actualites/brouillon');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    /**
     * For this test, the pagination size is forced to ease understanding.
     *
     * @dataProvider dataProviderIsPaginationValid
     */
    public function testIsPaginationValid(bool $expected, int $articlesCount, int $requestedPageNumber)
    {
        $reflectionMethod = new \ReflectionMethod(ArticleController::class, 'isPaginationValid');
        $reflectionMethod->setAccessible(true);

        $articleController = $this->getMockBuilder(ArticleController::class)
            ->setMethods(['isPaginationValid'])
            ->getMock()
        ;

        $this->assertEquals($expected, $reflectionMethod->invoke($articleController, $articlesCount, $requestedPageNumber, 5));
    }

    public function dataProviderIsPaginationValid(): array
    {
        return [
            [false,  0,  1],
            [true,   1,  1],
            [true,   5,  1],
            [false,  5,  2],
            [true,   6,  1],
            [true,   6,  2],
        ];
    }

    public function testRssFeed()
    {
        $this->client->request(Request::METHOD_GET, '/feed.xml');

        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertSame('application/rss+xml', $response->headers->get('Content-Type'));
    }

    public function testRedirectArticleTribunesToOpinions()
    {
        $this->client->request(Request::METHOD_GET, '/articles/tribunes/mes-opinions');

        $this->assertClientIsRedirectedTo('/articles/opinions/mes-opinions', $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/articles/tribunes/not-exist');

        $this->assertClientIsRedirectedTo('/articles/opinions/not-exist', $this->client, false, true);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testDisplayCTALink()
    {
        $this->client->request(Request::METHOD_GET, '/articles/opinions/mes-opinions');

        $this->assertContains('<a href="http://www.google.fr" class="category category--opinions">', $this->client->getResponse()->getContent());
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
