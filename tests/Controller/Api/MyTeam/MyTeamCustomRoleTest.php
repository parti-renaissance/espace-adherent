<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\MyTeam;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadMyTeamData;
use App\Entity\MyTeam\Member;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Repository\MyTeam\MemberRepository;
use App\Scope\FeatureEnum;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class MyTeamCustomRoleTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private ?MemberRepository $memberRepository;
    private ?DelegatedAccessRepository $delegatedAccessRepository;

    public function testCreateMemberWithMyTeamAddsCustomRole(): void
    {
        $response = $this->performRequest(
            Request::METHOD_POST,
            '/api/v3/my_team_members?scope=president_departmental_assembly',
            [
                'team' => '7fab9d6c-71a1-4257-b42b-c6b9b2350a26',
                'adherent' => 'acc73b03-9743-47d8-99db-5a6c6f55ad67',
                'role' => 'mobilization_manager',
                'scope_features' => [FeatureEnum::MY_TEAM],
            ]
        );

        $this->assertCustomRolePresence($response['uuid'], true);
    }

    public function testCreateMemberWithoutMyTeamDoesNotAddCustomRole(): void
    {
        $response = $this->performRequest(
            Request::METHOD_POST,
            '/api/v3/my_team_members?scope=president_departmental_assembly',
            [
                'team' => '7fab9d6c-71a1-4257-b42b-c6b9b2350a26',
                'adherent' => '69fcc468-598a-49ac-a651-d4d3ee856446',
                'role' => 'mobilization_manager',
                'scope_features' => [FeatureEnum::CONTACTS],
            ]
        );

        $this->assertCustomRolePresence($response['uuid'], false);
    }

    public function testUpdateMemberToAddMyTeamAddsCustomRole(): void
    {
        $memberUuid = LoadMyTeamData::MEMBER_3_UUID;

        $this->performRequest(
            Request::METHOD_PUT,
            \sprintf('/api/v3/my_team_members/%s?scope=president_departmental_assembly', $memberUuid),
            [
                'adherent' => 'a9fc8d48-6f57-4d89-ae73-50b3f9b586f4',
                'role' => 'logistics_manager',
                'scope_features' => [FeatureEnum::MY_TEAM],
            ],
            200
        );

        $this->assertCustomRolePresence($memberUuid, true);
    }

    private function performRequest(string $method, string $uri, array $payload, int $expectedStatusCode = 201): array
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
            $method,
            $uri,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame($expectedStatusCode);

        return json_decode($this->client->getResponse()->getContent(), true) ?? [];
    }

    private function assertCustomRolePresence(string $memberUuid, bool $shouldBePresent): void
    {
        $this->manager->clear();
        /** @var Member $member */
        $member = $this->memberRepository->findOneByUuid($memberUuid);
        $delegatedAccess = $this->findDelegatedAccess($member);

        if ($shouldBePresent) {
            $this->assertContains(FeatureEnum::MY_TEAM_CUSTOM_ROLE, $member->getScopeFeatures());
            $this->assertContains(FeatureEnum::MY_TEAM_CUSTOM_ROLE, $delegatedAccess->getScopeFeatures());
        } else {
            $this->assertNotContains(FeatureEnum::MY_TEAM_CUSTOM_ROLE, $member->getScopeFeatures());
            $this->assertNotContains(FeatureEnum::MY_TEAM_CUSTOM_ROLE, $delegatedAccess->getScopeFeatures());
        }
    }

    private function findDelegatedAccess(Member $member)
    {
        return $this->delegatedAccessRepository->findOneBy([
            'delegated' => $member->getAdherent(),
            'delegator' => $member->getTeam()->getOwner(),
            'type' => $member->getTeam()->getScope(),
        ]);
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
