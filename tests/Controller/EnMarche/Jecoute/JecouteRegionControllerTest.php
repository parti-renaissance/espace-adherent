<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche\Jecoute;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class JecouteRegionControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideAdherentsWithNoAccess')]
    public function testCannotEditJecouteRegion(string $adherentEmail)
    {
        $this->authenticateAsAdherent($this->client, $adherentEmail);

        $this->client->request(Request::METHOD_GET, '/espace-candidat/campagne/editer');

        self::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public static function provideAdherentsWithNoAccess(): iterable
    {
        yield ['benjyd@aol.com'];
        yield ['michelle.dufour@example.ch'];
        yield ['luciole1989@spambox.fr'];   // has a department as candidate managed area
        yield ['francis.brioul@yahoo.com']; // has a canton as candidate managed area
    }

    public static function provideAdherentsWithAccess(): iterable
    {
        yield ['jacques.picard@en-marche.fr', 'Espace candidat'];  // has a region as candidate managed area
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
