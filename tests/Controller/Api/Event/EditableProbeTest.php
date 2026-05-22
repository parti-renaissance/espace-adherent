<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class EditableProbeTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    // CLIENT_08 = "J'écoute", supports jemarche_app scope, GrantTypeEnum::PASSWORD
    private const CLIENT_UUID = LoadClientData::CLIENT_08_UUID;
    private const CLIENT_SECRET = '4THZGbOfHJvRHk8bHdtZP3BTrMWFod6bOZb2mY3wLE=';

    public function testMilitantEventEditableFieldWithAndWithoutScopeParam(): void
    {
        $accessToken = $this->getAccessToken(
            self::CLIENT_UUID,
            self::CLIENT_SECRET,
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'benjyd@aol.com',
            'secret!12345',
        );

        $authHeaders = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ];

        // Step 1: Create a militant event
        $this->client->request(
            Request::METHOD_POST,
            '/api/v3/events?scope=militant',
            [],
            [],
            $authHeaders,
            json_encode([
                'name' => 'Probe apero militant',
                'category' => 'kiosque',
                'description' => 'Probe description',
                'begin_at' => '2030-01-29 16:30:00',
                'finish_at' => '2030-01-30 16:30:00',
                'capacity' => 10,
                'is_national' => false,
                'mode' => 'online',
                'visio_url' => 'https://en-marche.fr/reunions/123',
                'time_zone' => 'Europe/Paris',
            ])
        );

        self::assertResponseStatusCodeSame(201);
        $created = json_decode($this->client->getResponse()->getContent(), true);
        $uuid = $created['uuid'];
        self::assertNotEmpty($uuid, 'Event UUID must not be empty');

        $authorScope = $created['author_scope'] ?? 'KEY_MISSING';
        echo "\n[PROBE] author_scope in POST response: $authorScope\n";

        // Check author_scope stored in DB directly
        $eventInDb = $this->manager->getConnection()->fetchAssociative(
            'SELECT author_scope, author_id FROM events WHERE uuid = ?',
            [$uuid]
        );
        echo '[PROBE] author_scope in DB: '.($eventInDb['author_scope'] ?? 'NULL')."\n";
        echo '[PROBE] author_id in DB: '.($eventInDb['author_id'] ?? 'NULL')."\n";

        // Step 2: GET the event WITH ?scope=militant
        $this->client->request(
            Request::METHOD_GET,
            "/api/v3/events/$uuid?scope=militant",
            [],
            [],
            $authHeaders,
        );

        self::assertResponseStatusCodeSame(200);
        $responseWithScope = json_decode($this->client->getResponse()->getContent(), true);
        $editableWithScope = $responseWithScope['editable'] ?? 'KEY_MISSING';
        $authorScopeRead = $responseWithScope['author_scope'] ?? 'KEY_MISSING';
        echo '[PROBE] editable WITH ?scope=militant: '.var_export($editableWithScope, true)."\n";
        echo "[PROBE] author_scope in GET response: $authorScopeRead\n";
        echo '[PROBE] Full GET with scope response keys: '.implode(', ', array_keys($responseWithScope))."\n";

        // Step 3: GET the event WITHOUT ?scope=
        $this->client->request(
            Request::METHOD_GET,
            "/api/v3/events/$uuid",
            [],
            [],
            $authHeaders,
        );

        self::assertResponseStatusCodeSame(200);
        $responseWithoutScope = json_decode($this->client->getResponse()->getContent(), true);
        $editableWithoutScope = $responseWithoutScope['editable'] ?? 'KEY_MISSING';
        echo '[PROBE] editable WITHOUT ?scope=: '.var_export($editableWithoutScope, true)."\n";

        // Assertions — these SHOULD pass but reportedly DON'T (that's the bug to confirm)
        self::assertTrue(
            $editableWithScope,
            \sprintf(
                '[BUG] editable=false with ?scope=militant for own event. author_scope=%s',
                $authorScope
            )
        );
        self::assertTrue(
            $editableWithoutScope,
            \sprintf(
                '[BUG] editable=false without ?scope= for own event. author_scope=%s',
                $authorScope
            )
        );
    }
}
