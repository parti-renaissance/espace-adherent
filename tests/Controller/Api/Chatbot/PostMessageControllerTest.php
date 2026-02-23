<?php

declare(strict_types=1);

namespace Tests\Controller\Api\Chatbot;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Chatbot\Message;
use App\Entity\Chatbot\Thread;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\AI\Platform\Message\Role;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Chatbot\DummyAgent;

#[Group('functional')]
#[Group('api')]
class PostMessageControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        DummyAgent::reset();
    }

    protected function tearDown(): void
    {
        DummyAgent::reset();

        parent::tearDown();
    }

    public function testUnauthenticatedUserIsRejected(): void
    {
        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'message' => 'Hello',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_UNAUTHORIZED, $this->client->getResponse());
    }

    public function testUserWithoutCanaryRoleIsRejected(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'carl999@example.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'message' => 'Hello',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testInvalidJsonReturns400(): void
    {
        $accessToken = $this->authenticateAsCanaryTester();

        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], 'invalid-json');

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function testMissingMessageReturns400(): void
    {
        $accessToken = $this->authenticateAsCanaryTester();

        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([]));

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function testEmptyMessageReturns400(): void
    {
        $accessToken = $this->authenticateAsCanaryTester();

        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'message' => '   ',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function testNewChatPersistsUserMessageAndBotResponse(): void
    {
        $accessToken = $this->authenticateAsCanaryTester();

        DummyAgent::willReturn(new TextResult('Bonjour, comment puis-je vous aider ?'));

        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'message' => 'Bonjour le bot',
        ]));

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_OK, $response);

        $threadUuid = $response->headers->get('X-Chatbot-Thread-UUID');
        $this->assertNotEmpty($threadUuid, 'Response should contain X-Chatbot-Thread-UUID header');

        $this->manager->clear();
        $thread = $this->getRepository(Thread::class)->findOneBy(['uuid' => $threadUuid]);
        $this->assertNotNull($thread, 'Thread should be persisted in database');

        $messages = $thread->messages->toArray();
        $this->assertCount(2, $messages, 'Thread should have user message + bot response');

        $userMessages = array_values(array_filter($messages, fn (Message $m) => Message::ROLE_USER === $m->role));
        $this->assertCount(1, $userMessages);
        self::assertSame('Bonjour le bot', $userMessages[0]->content);

        $botMessages = array_values(array_filter($messages, fn (Message $m) => Message::ROLE_ASSISTANT === $m->role));
        $this->assertCount(1, $botMessages);
        self::assertSame('Bonjour, comment puis-je vous aider ?', $botMessages[0]->content);
    }

    public function testLargeThreadContextIsTruncated(): void
    {
        $accessToken = $this->authenticateAsCanaryTester();

        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_1_UUID);
        $thread = new Thread($adherent);
        for ($i = 1; $i <= 9; ++$i) {
            $date = new \DateTimeImmutable('-'.(20 - $i).' minutes');
            $thread->addUserMessage("User question $i", $date);
            $thread->addAssistantMessage("Bot answer $i", $date->modify('+30 seconds'));
        }
        $this->manager->persist($thread);
        $this->manager->flush();

        DummyAgent::willReturn(new TextResult('Réponse finale'));

        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'thread_id' => $thread->getUuid()->toString(),
            'message' => 'Final question',
        ]));

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_OK, $response);

        $calls = DummyAgent::getCalls();
        self::assertCount(1, $calls);
        $contextBag = $calls[0]->getMessages();

        self::assertCount(15, $contextBag);
        self::assertSame(Role::User, $contextBag[0]->getRole());
        self::assertSame('User question 3', $contextBag[0]->getContent()[0]->getText());
        self::assertSame(Role::User, $contextBag[14]->getRole());
        self::assertSame('Final question', $contextBag[14]->getContent()[0]->getText());
    }

    public function testContinueExistingThread(): void
    {
        $accessToken = $this->authenticateAsCanaryTester();

        DummyAgent::willReturn(new TextResult('Réponse 1'));

        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'message' => 'Premier message',
        ]));

        $threadUuid = $this->client->getResponse()->headers->get('X-Chatbot-Thread-UUID');
        $this->assertNotEmpty($threadUuid);

        DummyAgent::willReturn(new TextResult('Réponse 2'));

        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'thread_id' => $threadUuid,
            'message' => 'Deuxième message',
        ]));

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_OK, $response);

        $calls = DummyAgent::getCalls();
        self::assertCount(2, $calls);
        $secondCallBag = $calls[1]->getMessages();

        self::assertCount(3, $secondCallBag);
        self::assertSame(Role::User, $secondCallBag[0]->getRole());
        self::assertSame('Premier message', $secondCallBag[0]->getContent()[0]->getText());

        self::assertSame(Role::Assistant, $secondCallBag[1]->getRole());
        self::assertSame('Réponse 1', $secondCallBag[1]->getContent());

        self::assertSame(Role::User, $secondCallBag[2]->getRole());
        self::assertSame('Deuxième message', $secondCallBag[2]->getContent()[0]->getText());

        $returnedThreadUuid = $response->headers->get('X-Chatbot-Thread-UUID');
        self::assertSame($threadUuid, $returnedThreadUuid);

        $this->manager->clear();
        $thread = $this->getRepository(Thread::class)->findOneBy(['uuid' => $threadUuid]);
        $this->assertNotNull($thread);

        $userMessages = array_values(array_filter(
            $thread->messages->toArray(),
            fn (Message $m) => Message::ROLE_USER === $m->role,
        ));

        $this->assertCount(2, $userMessages);
    }

    public function testBotErrorDoesNotPreventResponse(): void
    {
        $accessToken = $this->authenticateAsCanaryTester();

        DummyAgent::willThrow(new \RuntimeException('API Error'));

        $this->client->request('POST', '/api/v3/ai/chat', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ], json_encode([
            'message' => 'Message provoquant une erreur',
        ]));

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_OK, $response);

        $threadUuid = $response->headers->get('X-Chatbot-Thread-UUID');

        $this->manager->clear();
        $thread = $this->getRepository(Thread::class)->findOneBy(['uuid' => $threadUuid]);
        $this->assertNotNull($thread);

        $userMessages = array_values(array_filter(
            $thread->messages->toArray(),
            fn (Message $m) => Message::ROLE_USER === $m->role,
        ));
        $this->assertCount(1, $userMessages);
        self::assertSame('Message provoquant une erreur', $userMessages[0]->content);

        $botMessages = array_values(array_filter(
            $thread->messages->toArray(),
            fn (Message $m) => Message::ROLE_ASSISTANT === $m->role,
        ));
        $this->assertCount(0, $botMessages);
    }

    private function authenticateAsCanaryTester(): string
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_1_UUID);
        $adherent->canaryTester = true;
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
