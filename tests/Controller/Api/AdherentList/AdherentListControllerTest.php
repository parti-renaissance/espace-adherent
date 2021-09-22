<?php

namespace Tests\App\Controller\Api\AdherentList;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use App\Scope\ScopeEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Helper\PHPUnitHelper;

/**
 * @group functional
 * @group api
 */
class AdherentListControllerTest extends AbstractWebCaseTest
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private const URL = '/api/v3/adherents';

    public function testAnUnauthenticatedUserCannotRequestAdherentsEndpoint(): void
    {
        $this->client->request(Request::METHOD_GET, self::URL);

        $this->assertResponseStatusCode(Response::HTTP_UNAUTHORIZED, $this->client->getResponse());
    }

    public function testBadRequestForMissingScopeParam(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, self::URL, [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function testAsLoggedAdherentICanRequestAdherentsOfMyZones(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, self::URL, ['scope' => ScopeEnum::REFERENT], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);
        $response = $this->client->getResponse();

        $this->isSuccessful($response);

        $content = json_decode($response->getContent(), true);

        self::assertSame(4, $content['metadata']['count']);
        PHPUnitHelper::assertArraySubset(
            [
                'postal_code' => '77000',
                'city' => 'Melun',
                'country' => 'FR',
                'first_name' => 'Francis',
                'last_name' => 'Brioul',
                'gender' => 'male',
                'interests' => [],
                'city_code' => '77288',
                'department_code' => '77',
                'department' => 'Seine-et-Marne',
                'region_code' => '11',
                'region' => 'Île-de-France',
                'sms_subscription' => false,
                'email_subscription' => false,
            ],
            $content['items'][0]
        );
    }

    public function testAsLoggedAdherentICanRequestAdherentsOfMyWithFilters(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $filters = [
            'firstName' => 'Francis',
            'lastName' => 'Brioul',
            'gender' => 'male',
            'registered' => [
                'start' => '2016-01-01',
                'end' => '2042-01-01',
            ],
            'age' => [
                'min' => 18,
                'max' => 100,
            ],
            'isCommitteeMember' => '1',
            'isCertified' => '0',
            'emailSubscription' => '0',
            'smsSubscription' => '0',
        ];

        $this->client->request(Request::METHOD_GET, self::URL.'?'.http_build_query($filters), ['scope' => ScopeEnum::REFERENT], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);
        $response = $this->client->getResponse();

        self::assertJson($response->getContent());

        $this->isSuccessful($response);

        $content = json_decode($response->getContent(), true);

        self::assertSame(1, $content['metadata']['count']);

        PHPUnitHelper::assertArraySubset(
            [
                'postal_code' => '77000',
                'city' => 'Melun',
                'country' => 'FR',
                'first_name' => 'Francis',
                'last_name' => 'Brioul',
                'gender' => 'male',
                'interests' => [],
                'city_code' => '77288',
                'department_code' => '77',
                'department' => 'Seine-et-Marne',
                'region_code' => '11',
                'region' => 'Île-de-France',
                'sms_subscription' => false,
                'email_subscription' => false,
            ],
            $content['items'][0]
        );
    }
}
