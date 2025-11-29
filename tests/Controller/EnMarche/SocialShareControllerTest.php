<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('controller')]
class SocialShareControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public function testList()
    {
        $this->client->request(Request::METHOD_GET, '/jepartage');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testShow()
    {
        $this->client->request(Request::METHOD_GET, '/jepartage/culture');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }
}
