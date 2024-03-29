<?php

namespace Tests\App\Controller\EnMarche;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class ProgramControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideActions')]
    public function testSuccessfulActions(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->isSuccessful($this->client->getResponse());
    }

    public static function provideActions(): \Generator
    {
        yield ['/emmanuel-macron/le-programme'];
        yield ['/emmanuel-macron/le-programme/produire-en-france-et-sauver-la-planete'];
        yield ['/emmanuel-macron/le-programme/eduquer-tous-nos-enfants'];
    }

    #[DataProvider('provideRedirectActions')]
    public function testRedirectActions(string $path)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertClientIsRedirectedTo('/emmanuel-macron/le-programme', $this->client, false, true);
    }

    public static function provideRedirectActions(): \Generator
    {
        yield ['/programme'];
        yield ['/le-programme'];
    }

    public function testProposalDraft()
    {
        $this->client->request(Request::METHOD_GET, '/emmanuel-macron/le-programme/mieux-vivre-de-son-travail');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
    }
}
