<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadArticleData;
use AppBundle\DataFixtures\ORM\LoadPageData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class RedirectingControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideUrlsAndRedirections
     */
    public function testRemoveTrailingSlashAction(string $uri, string $redirectUri)
    {
        $this->client->request(Request::METHOD_GET, $uri);

        $this->assertResponseStatusCode(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse());
        $this->assertClientIsRedirectedTo($redirectUri, $this->client);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function provideUrlsAndRedirections()
    {
        yield 'Emmanuel Macron' => ['/emmanuel-macron/', '/emmanuel-macron'];
        yield 'Le mouvement' => ['/le-mouvement/', '/le-mouvement'];
        yield 'Actualités' => ['/articles/actualites/', '/articles/actualites'];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadPageData::class,
            LoadArticleData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
