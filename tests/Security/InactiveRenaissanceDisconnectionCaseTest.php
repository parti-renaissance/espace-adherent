<?php

declare(strict_types=1);

namespace Tests\App\Security;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('time-sensitive')]
#[Group('functional')]
class InactiveRenaissanceDisconnectionCaseTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testLogoutInactiveAdmin()
    {
        $this->markTestSkipped('Waiting for compatibility of Symfony/phpunit-bridge with PHPUnit 10 @see https://github.com/symfony/symfony/issues/49069');

        $this->authenticateAsAdmin($this->client);

        $this->client->request(Request::METHOD_GET, '/app/media/list');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // wait
        sleep(1900);

        // go to another page
        $this->client->request(Request::METHOD_GET, '/dashboard');

        // should be redirected to log out
        $this->assertClientIsRedirectedTo('http://test.renaissance.code/deconnexion', $this->client);
    }

    public function testNoLogoutInactiveAdherent()
    {
        $this->markTestSkipped('Waiting for compatibility of Symfony/phpunit-bridge with PHPUnit 10 @see https://github.com/symfony/symfony/issues/49069');

        $this->makeEMClient();
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        // wait
        sleep(1900);

        // go to another page
        $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        // status code should be 200 OK, because there is no redirection to disconnect
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }
}
