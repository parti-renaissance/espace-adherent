<?php

declare(strict_types=1);

namespace Tests\App\Controller;

use League\Glide\Signatures\Signature;
use League\Glide\Signatures\SignatureFactory;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;

#[Group('functional')]
#[Group('controller')]
class AssetsControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    /** @var Signature */
    private $signature;

    public function testAssetWithSignatureIsFound()
    {
        $this->client->request(Request::METHOD_GET, '/assets/10decembre.jpg', [
            's' => $this->signature->generateSignature('/assets/10decembre.jpg', []),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testAssetWithoutSignatureIsNotFound()
    {
        $this->client->request(Request::METHOD_GET, '/assets/10decembre.jpg');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testInvalidAssetWithSignatureIsNotFound()
    {
        $this->client->request(Request::METHOD_GET, '/assets/invalid.jpg', [
            's' => $this->signature->generateSignature('/assets/invalid.jpg', []),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testStaticMapsWithWrongQuery()
    {
        $this->client->request(Request::METHOD_GET, '/maps/47.3950813');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Query should have complete coordinates parameters'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47.,8.');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Coordinates should have a precision of at least 1 digit'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47.39508135,8.3361425');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Coordinates should have a precision of max 7 digits'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47.3950813;8.3361425');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Coordinates separator should be a comma'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47,3950813,8,3361425');
        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Precision separator should be a dot'
        );

        $this->client->request(Request::METHOD_GET, '/maps/47.39508a3,8.3361425');

        $this->assertResponseStatusCode(
            Response::HTTP_NOT_FOUND,
            $this->client->getResponse(),
            'Foreign characters are not allowed'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->signature = SignatureFactory::create($this->getParameter('kernel.secret'));
    }

    protected function tearDown(): void
    {
        $this->signature = null;

        parent::tearDown();
    }
}
