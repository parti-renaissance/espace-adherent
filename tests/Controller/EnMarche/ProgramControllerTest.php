<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadPageData;
use AppBundle\DataFixtures\ORM\LoadProposalData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class ProgramControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideActions
     */
    public function testSuccessfulActions(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function provideActions()
    {
        yield ['/emmanuel-macron/le-programme'];
        yield ['/emmanuel-macron/le-programme/produire-en-france-et-sauver-la-planete'];
        yield ['/emmanuel-macron/le-programme/eduquer-tous-nos-enfants'];
    }

    /**
     * @dataProvider provideRedirectActions
     */
    public function testRedirectActions(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertClientIsRedirectedTo('/emmanuel-macron/le-programme', $this->client);
    }

    public function provideRedirectActions()
    {
        yield ['/programme'];
        yield ['/le-programme'];
    }

    public function testProposalDraft()
    {
        $this->client->request(Request::METHOD_GET, '/emmanuel-macron/le-programme/mieux-vivre-de-son-travail');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadPageData::class,
            LoadProposalData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
