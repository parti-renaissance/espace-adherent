<?php

namespace Tests\AppBundle\Controller\EnMarche;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group controller
 */
class ObsoleteControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }

    /**
     * @dataProvider provideActions
     */
    public function testActions(string $path, bool $permanent = false)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode($permanent ? Response::HTTP_GONE : Response::HTTP_NOT_FOUND, $this->client);
    }

    public function provideActions()
    {
        yield ['/emmanuel-macron/desintox'];
        yield ['/emmanuel-macron/desintox/heritier-hollande-traite-quiquennat'];
    }
}
