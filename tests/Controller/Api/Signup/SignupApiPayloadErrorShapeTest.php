<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Signup;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * Cross-endpoint contract: the three signup endpoints relying on #[MapRequestPayload]
 * must surface the SAME Symfony-native error shape (RFC 7807) for the SAME class of
 * payload failure. A divergence here would mean one controller wires the attribute
 * differently — that is exactly the regression this test must catch.
 */
#[Group('functional')]
#[Group('api')]
class SignupApiPayloadErrorShapeTest extends AbstractApiTestCase
{
    use ControllerTestTrait;

    private const CLIENT_IP = '127.0.0.1';

    /** @return iterable<string, array{string, array<string, mixed>}> */
    public static function provideValidPayloadByEndpoint(): iterable
    {
        // Each payload satisfies the DTO so a single-field tweak (below) isolates one failure mode.
        yield '/api/signup/activate' => ['/api/signup/activate', ['email' => 'shape-activate@example.test', 'code' => '123']];
        yield '/api/signup/resend-code' => ['/api/signup/resend-code', ['email' => 'shape-resend@example.test']];
    }

    #[DataProvider('provideValidPayloadByEndpoint')]
    public function testMalformedJsonProducesUniformBadRequestShapeAcrossEndpoints(string $url, array $_): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            '{"email": "broken", '
        );

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        SignupApiErrorAssertions::assertBadRequestErrorShape($this->client->getResponse());
    }

    #[DataProvider('provideValidPayloadByEndpoint')]
    public function testInvalidEmailProducesUniformValidationShapeAcrossEndpoints(string $url, array $payload): void
    {
        // Same kind of constraint failure (invalid email) → same envelope on all three endpoints.
        $payload['email'] = 'not-an-email';
        $this->post($url, $payload);

        $this->assertResponseStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse());
        SignupApiErrorAssertions::assertValidationErrorShape($this->client->getResponse());
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::getContainer()->get('limiter.signup_code_attempt')->create(self::CLIENT_IP)->reset();
    }

    private function post(string $url, array $payload): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            json_encode($payload)
        );
    }
}
