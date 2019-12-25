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
uuid;first_name;last_name;email_address;phone;address;postal_code;city_name;district;gender;birthdate;latitude;longitude;interests;sms_mms
a046adbe-9c7b-56a9-a676-6151a6785dda;Jacques;Picard;jacques.picard@en-marche.fr;+33187264236;"36 rue de la Paix";75008;"Paris 8e";08;male;03-04-1953;48.869946;2.329719;;0
29461c49-6316-5be1-9ac3-17816bf2d819;Lucie;Olivera;luciole1989@spambox.fr;+33727363643;"13 boulevard des Italiens";75009;"Paris 9e";09;female;17-09-1989;48.871323;2.335376;jeunesse;0
CONTENT;

        $this->assertRegExp(sprintf('/%s/', preg_quote($regex)), $responseContent);

        // Ensure adherents without subscription type 'municipal_email' isn't exported
        $this->assertNotContains('gisele-berthoux@caramail.com', $responseContent);
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
