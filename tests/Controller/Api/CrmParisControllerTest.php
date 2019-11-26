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

        ob_start();
        $this->client->request(Request::METHOD_GET, '/api/crm-paris/adherents', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $acessToken",
        ]);

        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('none', $response->headers->get('Content-Encoding'));
        self::assertSame('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertRegExp(
            '/^attachment; filename="[\d]{14}-adherents.csv"$/',
            $response->headers->get('Content-Disposition')
        );

        $regex = <<<CONTENT
uuid,first_name,last_name
a046adbe-9c7b-56a9-a676-6151a6785dda,Jacques,Picard
29461c49-6316-5be1-9ac3-17816bf2d819,Lucie,Olivera
25e75e2f-2f73-4f51-8542-bd511ba6a945,Patrick,Bialès
2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf,Referent75and77,Referent75and77
1ebee762-4dc1-42f6-9884-1c83ba9c6d71,Coordinatrice,"CITIZEN PROJECT"
918f07e5-676b-49c0-b76d-72ce01cb2404,Député,"PARIS I"
ccd87fb0-7d98-433f-81e1-3dd8b14f79c0,Député,"CHLI FDESIX"
CONTENT;

        $this->assertRegExp(sprintf('/%s/', $regex), $responseContent);
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
