<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadUserData;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class AdherentApiSecurityTest extends AbstractApiTestCase
{
    use ApiControllerTestTrait;
    use ControllerTestTrait;

    private const string SIMPLE_USER_EMAIL = 'simple-user@example.ch';
    private const string SIMPLE_USER_UUID = LoadUserData::USER_1_UUID;
    private const string OTHER_ADHERENT_IN_PROJECTION_UUID = LoadAdherentData::ADHERENT_5_UUID;
    private const string OTHER_ADHERENT_UUID = LoadAdherentData::ADHERENT_2_UUID;

    public function testGetManagedUserReturnsUnauthorizedWithoutToken(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/api/v3/adherents/%s', self::OTHER_ADHERENT_IN_PROJECTION_UUID),
        );

        $this->assertResponseStatusCode(Response::HTTP_UNAUTHORIZED, $this->client->getResponse());
    }

    public function testGetManagedUserIsForbiddenForSimpleUser(): void
    {
        $this->requestWithSimpleUserToken(
            Request::METHOD_GET,
            \sprintf('/api/v3/adherents/%s', self::OTHER_ADHERENT_IN_PROJECTION_UUID),
            Scope::READ_PROFILE,
        );

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testGetElectReturnsOkForSelfAccess(): void
    {
        $this->requestWithSimpleUserToken(
            Request::METHOD_GET,
            \sprintf('/api/v3/adherents/%s/elect', self::SIMPLE_USER_UUID),
            Scope::READ_PROFILE,
        );

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testGetElectIsForbiddenForOtherUserWithReadProfileScope(): void
    {
        $this->requestWithSimpleUserToken(
            Request::METHOD_GET,
            \sprintf('/api/v3/adherents/%s/elect', self::OTHER_ADHERENT_UUID),
            Scope::READ_PROFILE,
        );

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testGetSensitiveDataIsForbiddenForSimpleUser(): void
    {
        $this->requestWithSimpleUserToken(
            Request::METHOD_GET,
            \sprintf('/api/v3/adherents/%s/sensitive-data?type=email', self::OTHER_ADHERENT_UUID),
            Scope::READ_PROFILE,
        );

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testGetDonationsIsForbiddenForSimpleUser(): void
    {
        $this->requestWithSimpleUserToken(
            Request::METHOD_GET,
            \sprintf('/api/v3/adherents/%s/donations', self::OTHER_ADHERENT_UUID),
            Scope::READ_PROFILE,
        );

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testPostProfileImageIsForbiddenWithoutWriteProfileScope(): void
    {
        $this->requestWithSimpleUserToken(
            Request::METHOD_POST,
            \sprintf('/api/v3/profile/%s/image', self::SIMPLE_USER_UUID),
            Scope::READ_PROFILE,
        );

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testPostProfileImageIsForbiddenForOtherUserWithWriteProfileScope(): void
    {
        $this->requestWithSimpleUserToken(
            Request::METHOD_POST,
            \sprintf('/api/v3/profile/%s/image', self::OTHER_ADHERENT_UUID),
            Scope::WRITE_PROFILE,
        );

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    private function requestWithSimpleUserToken(string $method, string $uri, string $scope): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            $scope,
            self::SIMPLE_USER_EMAIL,
            LoadAdherentData::DEFAULT_PASSWORD,
        );

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer '.$accessToken,
            ],
        );
    }
}
