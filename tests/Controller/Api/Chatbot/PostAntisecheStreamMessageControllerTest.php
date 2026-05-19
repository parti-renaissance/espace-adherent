<?php

declare(strict_types=1);

namespace Tests\Controller\Api\Chatbot;

use App\Chatbot\Antiseche\Exception\AntisecheException;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Chatbot\Message;
use App\Entity\Chatbot\Thread;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Chatbot\DummyAntisecheClient;

#[Group('functional')]
#[Group('api')]
class PostAntisecheStreamMessageControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private const ENDPOINT = '/api/v3/ai/bot/stream';

    protected function setUp(): void
    {
        parent::setUp();

        DummyAntisecheClient::reset();
        $this->resetAntisecheRateLimiter();
    }

    private function resetAntisecheRateLimiter(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_1_UUID);
        self::getContainer()
            ->get('limiter.bot_antiseche')
            ->create('antiseche_'.$adherent->getUuid()->toRfc4122())
            ->reset();
    }

    protected function tearDown(): void
    {
        DummyAntisecheClient::reset();

        parent::tearDown();
    }

    public function testUnauthenticatedUserIsRejected(): void
    {
        $this->client->request('POST', self::ENDPOINT, [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['message' => 'Hello']));

        $this->assertResponseStatusCode(Response::HTTP_UNAUTHORIZED, $this->client->getResponse());
    }

    public function testUserWithoutChatbotFeatureIsRejected(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'carl999@example.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->postJson(self::ENDPOINT, ['message' => 'Hello'], $accessToken);

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testBlankMessageReturns400(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        $this->postJson(self::ENDPOINT, ['message' => '   '], $accessToken);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function testHappyPathPersistsReplyAndExposesHeaders(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        DummyAntisecheClient::willStreamChunks([
            "event: token\ndata: {\"text\": \"Bon\"}\n\n",
            "event: token\ndata: {\"text\": \"jour\"}\n\n",
            "event: done\ndata: {\"reply\": \"Bonjour\"}\n\n",
        ]);

        $this->postJson(self::ENDPOINT, ['message' => 'Salut'], $accessToken);

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_OK, $response);
        self::assertStringStartsWith('text/event-stream', $response->headers->get('Content-Type'));

        $threadUuid = $response->headers->get('X-Chatbot-Thread-UUID');
        self::assertNotEmpty($threadUuid);

        $this->manager->clear();
        $thread = $this->getRepository(Thread::class)->findOneBy(['uuid' => $threadUuid]);
        self::assertNotNull($thread);

        $bot = array_values(array_filter(
            $thread->messages->toArray(),
            fn (Message $m) => Message::ROLE_ASSISTANT === $m->role,
        ));
        self::assertCount(1, $bot, 'Reply parsed from event: done should be persisted');
        self::assertSame('Bonjour', $bot[0]->content);
    }

    public function testUpstreamFailureReturns502BeforeStreamStarts(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        DummyAntisecheClient::willThrow(new AntisecheException('boom', statusCode: 503));

        $this->postJson(self::ENDPOINT, ['message' => 'Question'], $accessToken);

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_BAD_GATEWAY, $response);

        $payload = json_decode($response->getContent(), true);
        self::assertArrayHasKey('error', $payload);
    }

    public function testUpstreamFailureDoesNotPersistAssistantMessage(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        DummyAntisecheClient::willThrow(new AntisecheException('boom'));

        $this->postJson(self::ENDPOINT, ['message' => 'Question'], $accessToken);

        $this->assertResponseStatusCode(Response::HTTP_BAD_GATEWAY, $this->client->getResponse());

        $this->manager->clear();
        $thread = $this->getRepository(Thread::class)->findOneBy([], ['createdAt' => 'DESC']);
        self::assertNotNull($thread);

        $bot = array_values(array_filter(
            $thread->messages->toArray(),
            fn (Message $m) => Message::ROLE_ASSISTANT === $m->role,
        ));
        self::assertCount(0, $bot);
    }

    public function testStreamWithoutDoneEventPersistsNothing(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        DummyAntisecheClient::willStreamChunks([
            "event: token\ndata: {\"text\": \"partial\"}\n\n",
            "event: error\ndata: {\"detail\": \"LLM crashed\"}\n\n",
        ]);

        $this->postJson(self::ENDPOINT, ['message' => 'Question'], $accessToken);

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_OK, $response);

        $threadUuid = $response->headers->get('X-Chatbot-Thread-UUID');
        $this->manager->clear();
        $thread = $this->getRepository(Thread::class)->findOneBy(['uuid' => $threadUuid]);

        $bot = array_values(array_filter(
            $thread->messages->toArray(),
            fn (Message $m) => Message::ROLE_ASSISTANT === $m->role,
        ));
        self::assertCount(0, $bot, 'Partial / errored streams must not persist a reply');
    }

    public function testRateLimitReturns429(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        DummyAntisecheClient::willStreamChunks([
            "event: done\ndata: {\"reply\": \"ok\"}\n\n",
        ]);

        for ($i = 1; $i <= 20; ++$i) {
            $this->postJson(self::ENDPOINT, ['message' => "req $i"], $accessToken);
            $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        }

        $this->postJson(self::ENDPOINT, ['message' => '21ème'], $accessToken);

        $this->assertResponseStatusCode(Response::HTTP_TOO_MANY_REQUESTS, $this->client->getResponse());
    }

    private function postJson(string $uri, array $payload, ?string $token = null): void
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($token) {
            $headers['HTTP_AUTHORIZATION'] = "Bearer $token";
        }

        $this->client->request('POST', $uri, [], [], $headers, json_encode($payload));
    }

    private function authenticateWithChatbotAccess(): string
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_1_UUID);
        $adherent->setNationalRole(true);
        $this->manager->flush();

        return $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'michelle.dufour@example.ch',
            LoadAdherentData::DEFAULT_PASSWORD
        );
    }
}
