<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
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
class AdherentActivityControllerTest extends AbstractApiTestCase
{
    use ApiControllerTestTrait;
    use ControllerTestTrait;

    private const string ADHERENT_UUID = LoadAdherentData::ADHERENT_1_UUID;

    public function testGetActivityWithoutFiltersReturnsAllItems(): void
    {
        $payload = $this->requestActivity();

        self::assertGreaterThan(0, $payload['metadata']['total_items']);
        self::assertNotEmpty($payload['items']);
        foreach ($payload['items'] as $item) {
            self::assertArrayHasKey('source_type', $item);
            self::assertArrayHasKey('event_type', $item);
            self::assertContains($item['source_type'], ['hit', 'action_history']);
        }
    }

    public function testGetActivityFilteredByActionHistorySourceType(): void
    {
        $payload = $this->requestActivity(['source_type' => 'action_history']);

        self::assertGreaterThan(0, $payload['metadata']['total_items']);
        foreach ($payload['items'] as $item) {
            self::assertSame('action_history', $item['source_type']);
            self::assertNull($item['description'] ?? null, 'action_history items must not carry a description');
        }
    }

    public function testGetActivityFilteredByEventType(): void
    {
        $payload = $this->requestActivity(['source_type' => 'action_history', 'event_type' => 'login_success']);

        self::assertGreaterThan(0, $payload['metadata']['total_items']);
        foreach ($payload['items'] as $item) {
            self::assertSame('action_history', $item['source_type']);
            self::assertSame('login_success', $item['event_type']);
            self::assertSame('Connexion réussie', $item['event_label']);
        }
    }

    private function requestActivity(array $filters = []): array
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD,
        );

        $query = http_build_query(['scope' => 'president_departmental_assembly'] + $filters);
        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/api/v3/adherents/%s/activity?%s', self::ADHERENT_UUID, $query),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
        );

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        return json_decode($this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }
}
