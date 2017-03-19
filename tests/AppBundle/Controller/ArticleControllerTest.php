<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Controller\ArticleController;
use AppBundle\DataFixtures\ORM\LoadArticleData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadLiveLinkData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class ArticleControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @group functionnal
     */
    public function testArticlePublished()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/article/outre-mer');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertContains('<img src="/assets/images/article.jpg', $this->client->getResponse()->getContent());
    }

    /**
     * @group functionnal
     */
    public function testArticleWithoutImage()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/article/sans-image');

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertNotContains('<img src="/assets/images/article.jpg', $this->client->getResponse()->getContent());
    }

    /**
     * @group functionnal
     */
    public function testArticleDraft()
    {
        $this->client->request(Request::METHOD_GET, '/article/brouillon');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    /**
     * For this test, the pagination size is forced to ease understanding.
     *
     * @dataProvider dataProviderIsPaginationValid
     *
     * @param bool $expected
     * @param int  $articlesCount
     * @param int  $requestedPageNumber
     */
    public function testIsPaginationValid(bool $expected, int $articlesCount, int $requestedPageNumber)
    {
        $reflectionMethod = new \ReflectionMethod(ArticleController::class, 'isPaginationValid');
        $reflectionMethod->setAccessible(true);

        $articleController = $this->getMockBuilder(ArticleController::class)
            ->setMethods(['isPaginationValid'])
            ->getMock();

        $this->assertEquals($expected, $reflectionMethod->invoke($articleController, $articlesCount, $requestedPageNumber, 5));
    }

    /**
     * @return array
     */
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

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadArticleData::class,
            LoadHomeBlockData::class,
            LoadLiveLinkData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
