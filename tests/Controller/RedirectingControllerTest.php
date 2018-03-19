<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadArticleData;
use AppBundle\DataFixtures\ORM\LoadPageData;
use Symfony\Component\HttpFoundation\Request;
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

        $this->assertClientIsRedirectedTo($redirectUri, $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }

    public function provideUrlsAndRedirections()
    {
        yield 'Emmanuel Macron' => ['/emmanuel-macron/', '/emmanuel-macron'];
        yield 'Le mouvement' => ['/le-mouvement/', '/le-mouvement'];
        yield 'Actualités' => ['/articles/actualites/', '/articles/actualites'];
        yield 'Inscription' => ['/adhesion/', '/adhesion'];
        yield 'Inscription with parameters' => ['/adhesion/?param1=value1&param2=value2', '/adhesion?param1=value1&param2=value2'];
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
