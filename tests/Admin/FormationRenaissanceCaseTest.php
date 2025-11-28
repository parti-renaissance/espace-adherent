<?php

declare(strict_types=1);

namespace Tests\App\Admin;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractAdminWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class FormationRenaissanceCaseTest extends AbstractAdminWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('uriProvider')]
    public function testSuperAdminCanAccessFormationAdmin(string $uri): void
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');
        $this->client->request(Request::METHOD_GET, $uri);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public static function uriProvider(): iterable
    {
        yield ['/app/formation-path/list'];
        yield ['/app/formation-path/create'];
        yield ['/app/formation-axe/list'];
        yield ['/app/formation-axe/create'];
        yield ['/app/formation-module/list'];
        yield ['/app/formation-module/create'];
    }
}
