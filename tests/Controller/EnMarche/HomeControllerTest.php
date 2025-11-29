<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('home')]
class HomeControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public function testDynamicRedirections(): void
    {
        $this->client->request(Request::METHOD_GET, '/dynamic-redirection-301/?test=123');

        $this->assertClientIsRedirectedTo('/evenements', $this->client, false, true);

        $this->client->request(Request::METHOD_GET, '/dynamic-redirection-302');

        $this->assertClientIsRedirectedTo('/comites', $this->client);
    }
}
