<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\Formation;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class ListControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testREAdherentCanSeeFormations(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/formations');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $formations = $crawler->filter('h3');
        self::assertCount(2, $formations);
        self::assertSame('Première formation nationale', $formations->eq(0)->text());
        self::assertSame('Formation sans description', $formations->eq(1)->text());
    }

    public function testNonREAdherentCanNotSeeFormations(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/formations');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }
}
