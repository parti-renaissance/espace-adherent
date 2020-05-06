<?php

namespace Tests\App\Controller\Legislatives;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group legislatives
 */
class MapsControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCandidates()
    {
        $this->client->request(Request::METHOD_GET, $this->hosts['scheme'].'://'.$this->hosts['legislatives'].'/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testEvents()
    {
        $this->client->request(Request::METHOD_GET, $this->hosts['scheme'].'://'.$this->hosts['legislatives'].'/les-evenements');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init('legislatives');
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
