<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\MyTeam;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadDelegatedAccessData;
use App\DataFixtures\ORM\LoadMyTeamData;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\Member;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Repository\MyTeam\MemberRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class MyTeamMemberControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private ?MemberRepository $memberRepository;
    private ?DelegatedAccessRepository $delegatedAccessRepository;

    public function testCreateMember(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(
            Request::METHOD_POST,
            '/api/v3/my_team_members?scope=president_departmental_assembly',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
            json_encode([
                'team' => '7fab9d6c-71a1-4257-b42b-c6b9b2350a26',
                'adherent' => 'acc73b03-9743-47d8-99db-5a6c6f55ad67',
                'role' => 'mobilization_manager',
                'scope_features' => ['contacts', 'messages'],
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('uuid', $response);

        /** @var Member $member */
        $member = $this->memberRepository->findOneByUuid($response['uuid']);

        $this->assertNotNull($member);
        $this->assertNotNull($team = $member->getTeam());

        $delegatedAccess = $this->delegatedAccessRepository->findOneBy([
            'delegated' => $member->getAdherent(),
            'delegator' => $team->getOwner(),
            'type' => $team->getScope(),
        ]);

        $this->assertValidDelegatedAccesses($member, $delegatedAccess);
    }

    public function testCreateMemberWithExistingEMDelegatedAccess(): void
    {
        $delegatedAccess = $this->delegatedAccessRepository->findOneBy(['uuid' => LoadDelegatedAccessData::ACCESS_UUID_10]);

        $this->assertNotNull($delegatedAccess);
        $this->assertNotEmpty($delegatedAccess->getAccesses());
        $this->assertEmpty($delegatedAccess->getScopeFeatures());

        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(
            Request::METHOD_POST,
            '/api/v3/my_team_members?scope=president_departmental_assembly',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
            json_encode([
                'team' => '7fab9d6c-71a1-4257-b42b-c6b9b2350a26',
                'adherent' => '69fcc468-598a-49ac-a651-d4d3ee856446',
                'role' => 'mobilization_manager',
                'scope_features' => ['contacts', 'messages'],
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('uuid', $response);

        /** @var Member $member */
        $member = $this->memberRepository->findOneByUuid($response['uuid']);

        $this->assertNotNull($member);
        $this->assertNotNull($team = $member->getTeam());

        $delegatedAccess = $this->delegatedAccessRepository->findOneBy([
            'delegated' => $member->getAdherent(),
            'delegator' => $team->getOwner(),
            'type' => $team->getScope(),
        ]);

        $this->assertValidDelegatedAccesses($member, $delegatedAccess);
    }

    public function testEditMember(): void
    {
        $memberUuid = LoadMyTeamData::MEMBER_3_UUID;
        $member = $this->memberRepository->findOneByUuid($memberUuid);
        $delegatedAccess = $this->delegatedAccessRepository->findOneByUuid(LoadDelegatedAccessData::ACCESS_UUID_7);

        $this->assertValidDelegatedAccesses($member, $delegatedAccess);

        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(
            Request::METHOD_PUT,
            \sprintf('/api/v3/my_team_members/%s?scope=president_departmental_assembly', $memberUuid),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
            json_encode([
                'adherent' => 'a9fc8d48-6f57-4d89-ae73-50b3f9b586f4',
                'role' => 'logistics_manager',
                'scope_features' => ['events'],
            ])
        );

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('uuid', $response);
        $this->assertSame($memberUuid, $response['uuid']);

        $this->manager->clear();

        /** @var Member $member */
        $member = $this->memberRepository->findOneByUuid($response['uuid']);

        $this->assertNotNull($member);
        $this->assertNotNull($team = $member->getTeam());

        $delegatedAccess = $this->delegatedAccessRepository->findOneBy([
            'delegated' => $member->getAdherent(),
            'delegator' => $team->getOwner(),
            'type' => $team->getScope(),
        ]);

        $this->assertValidDelegatedAccesses($member, $delegatedAccess);
    }

    public function testDeleteMember(): void
    {
        $memberUuid = LoadMyTeamData::MEMBER_1_UUID;
        $member = $this->memberRepository->findOneByUuid($memberUuid);
        $delegatedAccess = $this->delegatedAccessRepository->findOneByUuid(LoadDelegatedAccessData::ACCESS_UUID_11);

        $this->assertNotNull($member);
        $this->assertNotNull($delegatedAccess);

        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(
            Request::METHOD_DELETE,
            \sprintf('/api/v3/my_team_members/%s?scope=president_departmental_assembly', $memberUuid),
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $accessToken"],
        );

        $this->assertResponseStatusCodeSame(204);

        $this->manager->clear();
        $member = $this->memberRepository->findOneByUuid($memberUuid);
        $delegatedAccess = $this->delegatedAccessRepository->findOneByUuid(LoadDelegatedAccessData::ACCESS_UUID_11);

        $this->assertNull($member);
        $this->assertNull($delegatedAccess);
    }

    public function testDeleteMemberWithExistingDelegatedAccess(): void
    {
        $memberUuid = LoadMyTeamData::MEMBER_3_UUID;
        $member = $this->memberRepository->findOneByUuid($memberUuid);
        $delegatedAccess = $this->delegatedAccessRepository->findOneByUuid(LoadDelegatedAccessData::ACCESS_UUID_7);

        $this->assertNotNull($member);
        $this->assertNotNull($delegatedAccess);
        $this->assertNotEmpty($delegatedAccess->getScopeFeatures());
        $this->assertNotEmpty($delegatedAccess->getAccesses());

        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(
            Request::METHOD_DELETE,
            \sprintf('/api/v3/my_team_members/%s?scope=president_departmental_assembly', $memberUuid),
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $accessToken"],
        );

        $this->assertResponseStatusCodeSame(204);

        $this->manager->clear();
        $member = $this->memberRepository->findOneByUuid(LoadMyTeamData::MEMBER_3_UUID);
        $delegatedAccess = $this->delegatedAccessRepository->findOneByUuid(LoadDelegatedAccessData::ACCESS_UUID_7);

        $this->assertNull($member);
        $this->assertNotNull($delegatedAccess);
        $this->assertEmpty($delegatedAccess->getScopeFeatures());
        $this->assertNotEmpty($delegatedAccess->getAccesses());
    }

    private function assertValidDelegatedAccesses(Member $member, DelegatedAccess $delegatedAccess): void
    {
        $this->assertNotNull($member);
        $this->assertNotNull($delegatedAccess);
        $this->assertNotNull($team = $member->getTeam());
        $this->assertSame($member->getAdherent(), $delegatedAccess->getDelegated());
        $this->assertSame($team->getOwner(), $delegatedAccess->getDelegator());
        $this->assertSame($team->getScope(), $delegatedAccess->getType());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->memberRepository = $this->getMyTeamMemberRepository();
        $this->delegatedAccessRepository = $this->getDelegatedAccessRepository();
    }

    protected function tearDown(): void
    {
        $this->memberRepository = null;
        $this->delegatedAccessRepository = null;

        parent::tearDown();
    }
}
