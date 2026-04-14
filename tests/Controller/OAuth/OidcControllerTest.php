<?php

declare(strict_types=1);

namespace Tests\App\Controller\OAuth;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class OidcControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testDiscoveryEndpointReturnsOidcMetadata(): void
    {
        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('user_vox_host'));

        $this->client->request(Request::METHOD_GET, '/.well-known/openid-configuration');
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));

        $metadata = json_decode((string) $response->getContent(), true);

        self::assertIsArray($metadata);
        foreach (['issuer', 'authorization_endpoint', 'token_endpoint', 'userinfo_endpoint', 'jwks_uri', 'scopes_supported', 'id_token_signing_alg_values_supported'] as $key) {
            self::assertArrayHasKey($key, $metadata, "Missing required OIDC metadata key: $key");
        }
        self::assertContains('openid', $metadata['scopes_supported']);
        self::assertContains('profile', $metadata['scopes_supported']);
        self::assertContains('email', $metadata['scopes_supported']);
        self::assertSame(['RS256'], $metadata['id_token_signing_alg_values_supported']);
    }

    public function testJwksEndpointReturnsPublicKey(): void
    {
        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('user_vox_host'));

        $this->client->request(Request::METHOD_GET, '/oauth/v2/jwks');
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $jwks = json_decode((string) $response->getContent(), true);

        self::assertIsArray($jwks);
        self::assertArrayHasKey('keys', $jwks);
        self::assertCount(1, $jwks['keys']);

        $jwk = $jwks['keys'][0];
        self::assertSame('RSA', $jwk['kty']);
        self::assertSame('sig', $jwk['use']);
        self::assertSame('RS256', $jwk['alg']);
        self::assertNotEmpty($jwk['kid']);
        self::assertNotEmpty($jwk['n']);
        self::assertNotEmpty($jwk['e']);
    }

    public function testUserinfoEndpointRequiresBearerToken(): void
    {
        $this->client->setServerParameter('HTTP_HOST', $this->getParameter('user_vox_host'));

        $this->client->request(Request::METHOD_GET, '/oauth/v2/userinfo');
        $response = $this->client->getResponse();

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
