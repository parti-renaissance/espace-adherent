<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadClarificationData;
use AppBundle\DataFixtures\ORM\LoadPageData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class DesintoxControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideActions
     */
    public function testActions(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
    }

    public function provideActions()
    {
        yield ['/emmanuel-macron/desintox'];
        yield ['/emmanuel-macron/desintox/heritier-hollande-traite-quiquennat'];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadPageData::class,
            LoadClarificationData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
