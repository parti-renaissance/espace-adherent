<?php

namespace Tests\App\Controller\EnMarche;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Helper\PHPUnitHelper;

/**
 * @group functional
 */
class CoalitionAuthorAutocompleteControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private $causeRepository;

    public function testNotCoalitionModeratorCannotAccess(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->xmlHttpRequest(Request::METHOD_GET, '/espace-coalition/author/autocompletion?search=oul');

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function testCoalitionModeratorCanAccess(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->xmlHttpRequest(Request::METHOD_GET, '/espace-coalition/author/autocompletion?search=oul%20franc');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $data);
        PHPUnitHelper::assertArraySubset([
            'uuid' => 'a9fc8d48-6f57-4d89-ae73-50b3f9b586f4',
            'first_name' => 'Francis',
            'last_name' => 'Brioul',
            'registered_at' => '25/01/2017',
            'is_adherent' => true,
            'is_female' => false,
        ], $data[0]);
        PHPUnitHelper::assertArraySubset([
            'uuid' => 'cd76b8cf-af20-4976-8dd9-eb067a2f30c7',
            'first_name' => 'Pierre',
            'last_name' => 'Kiroule',
            'registered_at' => '09/04/2017',
            'is_adherent' => true,
            'is_female' => false,
        ], $data[1]);
    }

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
}
