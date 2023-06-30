<?php

namespace Tests\App\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebTestCase as WebTestCase;

#[Group('functional')]
#[Group('controller')]
class ObsoleteControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideActions')]
    public function testActions(string $path, bool $permanent = false)
    {
        $this->client->request(Request::METHOD_GET, $path);

        $this->assertStatusCode($permanent ? Response::HTTP_GONE : Response::HTTP_NOT_FOUND, $this->client);
    }

    public static function provideActions(): \Generator
    {
        yield ['/emmanuel-macron/desintox'];
        yield ['/emmanuel-macron/desintox/heritier-hollande-traite-quiquennat'];
    }
}
