<?php

namespace Tests\AppBundle\Controller\Api;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\AppBundle\Controller\ControllerTestTrait;

class CrmParisControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testExportAdherentsCsv(): void
    {
        // The OAuth client asks for an access token
        $this->client->request('POST', '/oauth/v2/token', [
            'client_id' => '40bdd6db-e422-4153-819c-9973c09f9297',
            'client_secret' => 'cChiFrOxtYb4CgnKoYvV9evEcrOsk2hb9wvO73QLYyc=',
            'grant_type' => 'client_credentials',
            'scope' => 'crm_paris',
        ]);
        $response = $this->client->getResponse();
        $this->isSuccessful($response);

        $data = json_decode($response->getContent(), true);
        $acessToken = $data['access_token'];

        $this->client->request(Request::METHOD_GET, '/api/crm-paris/adherents', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $acessToken",
        ]);

        $this->isSuccessful($response = $this->client->getResponse());

        $this->assertEquals('OK', $response->getContent());
    }

    public function testAnonymousCanNotExportAdherentsCsv(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/crm-paris/adherents');
        $this->assertStatusCode(401, $this->client);

        $this->client->request(Request::METHOD_GET, '/api/crm-paris/adherents', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer 123abc456def',
        ]);
        $this->assertStatusCode(401, $this->client);
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
