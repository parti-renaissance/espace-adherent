<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\AdherentNote;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Adherent\Note\AdherentNote;
use App\Entity\Adherent\Note\AdherentNoteAuthor;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use App\Repository\Adherent\Note\AdherentNoteRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class AdherentNoteControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private const SCOPE = 'president_departmental_assembly';
    private const TARGET_ADHERENT_UUID = LoadAdherentData::ADHERENT_6_UUID;

    private ?AdherentNoteRepository $noteRepository = null;

    public function testGetNotesUnauthorized(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/api/v3/adherents/%s/notes?scope=%s', self::TARGET_ADHERENT_UUID, self::SCOPE)
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetNotes(): void
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
            Request::METHOD_GET,
            \sprintf('/api/v3/adherents/%s/notes?scope=%s', self::TARGET_ADHERENT_UUID, self::SCOPE),
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
    }

    public function testCreateNoteUnauthorized(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            \sprintf('/api/v3/adherents/%s/notes?scope=%s', self::TARGET_ADHERENT_UUID, self::SCOPE),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['content' => 'Test note'])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateNote(): void
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
            \sprintf('/api/v3/adherents/%s/notes?scope=%s', self::TARGET_ADHERENT_UUID, self::SCOPE),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
            json_encode(['content' => 'Une note de test.'])
        );

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('uuid', $response);
        $this->assertArrayHasKey('content', $response);
        $this->assertSame('Une note de test.', $response['content']);
        $this->assertArrayHasKey('author', $response);
        $this->assertArrayHasKey('modifiable', $response);
        $this->assertTrue($response['modifiable']);

        /** @var AdherentNote $note */
        $note = $this->noteRepository->findOneBy(['uuid' => $response['uuid']]);
        $this->assertNotNull($note);
        $this->assertSame('Une note de test.', $note->content);
        $this->assertSame(self::TARGET_ADHERENT_UUID, $note->targetAdherent->getUuid()->toString());
        $this->assertCount(1, $note->getAuthors());
        $this->assertSame(AdherentNoteAuthor::TYPE_ADD, $note->getAuthors()->first()->type);
    }

    public function testCreateNoteValidationFails(): void
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
            \sprintf('/api/v3/adherents/%s/notes?scope=%s', self::TARGET_ADHERENT_UUID, self::SCOPE),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
            json_encode(['content' => ''])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testEditNote(): void
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
            \sprintf('/api/v3/adherents/%s/notes?scope=%s', self::TARGET_ADHERENT_UUID, self::SCOPE),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
            json_encode(['content' => 'Contenu initial.'])
        );

        $this->assertResponseStatusCodeSame(201);
        $noteUuid = json_decode($this->client->getResponse()->getContent(), true)['uuid'];

        $this->client->request(
            Request::METHOD_PUT,
            \sprintf('/api/v3/adherents/%s/notes/%s?scope=%s', self::TARGET_ADHERENT_UUID, $noteUuid, self::SCOPE),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
            json_encode(['content' => 'Contenu modifié.'])
        );

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Contenu modifié.', $response['content']);
        $this->assertSame($noteUuid, $response['uuid']);

        $this->manager->clear();

        /** @var AdherentNote $note */
        $note = $this->noteRepository->findOneBy(['uuid' => $noteUuid]);
        $this->assertNotNull($note);
        $this->assertSame('Contenu modifié.', $note->content);
        $this->assertCount(2, $note->getAuthors());
        $this->assertSame(AdherentNoteAuthor::TYPE_EDIT, $note->getAuthors()->last()->type);
    }

    public function testEditNoteUnauthorized(): void
    {
        $this->client->request(
            Request::METHOD_PUT,
            \sprintf(
                '/api/v3/adherents/%s/notes/00000000-0000-0000-0000-000000000000?scope=%s',
                self::TARGET_ADHERENT_UUID,
                self::SCOPE
            ),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['content' => 'test'])
        );

        $this->assertResponseStatusCodeSame(401);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->noteRepository = $this->getRepository(AdherentNote::class);
    }

    protected function tearDown(): void
    {
        $this->noteRepository = null;

        parent::tearDown();
    }
}
