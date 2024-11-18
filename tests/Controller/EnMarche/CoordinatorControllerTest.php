<?php

namespace Tests\App\Controller\EnMarche;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('coordinator')]
class CoordinatorControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('providePages')]
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

    public static function providePages(): array
    {
        return [
            ['/espace-coordinateur/comites/list'],
        ];
    }
}
