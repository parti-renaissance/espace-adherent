<?php

namespace Tests\App\Controller\EnMarche;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('explainer')]
class ExplainerControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideActions')]
    public function testSuccessfulActions(string $path)
    {
        $crawler = $this->client->request(Request::METHOD_GET, $path);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(4, $crawler->filter('.explainer__articles > ul > li')->count());
    }

    public static function provideActions(): \Generator
    {
        yield ['/transformer-la-france'];
    }
}
