<?php

namespace Tests\App\Controller\EnMarche;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebCaseTest;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group coordinator
 */
class CoordinatorControllerTest extends AbstractEnMarcheWebCaseTest
{
    use ControllerTestTrait;

    /**
     * @dataProvider providePages
     */
    public function testCoordinatorBackendIsForbiddenForAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testCoordinatorBackendIsForbiddenForAdherentNotCoordinator($path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public function testCoordinatorCommitteeBackendIsAccessibleForCoordinator()
    {
        $this->authenticateAsAdherent($this->client, 'coordinateur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-coordinateur/comites/list');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function providePages(): array
    {
        return [
            ['/espace-coordinateur/comites/list'],
        ];
    }
}
