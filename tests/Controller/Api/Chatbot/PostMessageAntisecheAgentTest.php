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
use Symfony\AI\Platform\Result\StreamResult;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\Test\Chatbot\DummyAntisecheAgent;

#[Group('functional')]
#[Group('api')]
class PostMessageAntisecheAgentTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private const ENDPOINT = '/api/v3/ai/chat';

    protected function setUp(): void
    {
        parent::setUp();

        DummyAntisecheAgent::reset();
        $this->resetChatbotRateLimiter();
    }

    private function resetChatbotRateLimiter(): void
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_1_UUID);
        self::getContainer()
            ->get('limiter.bot_chatbot')
            ->create('chatbot_antiseche_'.$adherent->getUuid()->toRfc4122())
            ->reset();
    }

    protected function tearDown(): void
    {
        DummyAntisecheAgent::reset();

        parent::tearDown();
    }

    public function testHappyPathPersistsReply(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        DummyAntisecheAgent::willReturn(new StreamResult($this->yieldChunks(['Bon', 'jour'])));

        $this->postJson(['message' => 'Salut', 'agent_id' => 'antiseche'], $accessToken);

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
        self::assertCount(1, $bot);
        self::assertSame('Bonjour', $bot[0]->content);
    }

    public function testUpstreamFailureEmitsErrorEventAndDoesNotPersist(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        DummyAntisecheAgent::willThrow(new \RuntimeException('boom'));

        $this->postJson(['message' => 'Question', 'agent_id' => 'antiseche'], $accessToken);

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_OK, $response);

        $threadUuid = $response->headers->get('X-Chatbot-Thread-UUID');
        $this->manager->clear();
        $thread = $this->getRepository(Thread::class)->findOneBy(['uuid' => $threadUuid]);
        self::assertNotNull($thread);

        $bot = array_values(array_filter(
            $thread->messages->toArray(),
            fn (Message $m) => Message::ROLE_ASSISTANT === $m->role,
        ));
        self::assertCount(0, $bot);
    }

    public function testRateLimitReturns429(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        DummyAntisecheAgent::willReturn(new TextResult('ok'));

        for ($i = 1; $i <= 20; ++$i) {
            $this->postJson(['message' => "req $i", 'agent_id' => 'antiseche'], $accessToken);
            $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        }

        $this->postJson(['message' => '21ème', 'agent_id' => 'antiseche'], $accessToken);

        $this->assertResponseStatusCode(Response::HTTP_TOO_MANY_REQUESTS, $this->client->getResponse());
    }

    public function testMissingAgentIdReturns400(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        $this->postJson(['message' => 'Salut'], $accessToken);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function testUnknownAgentIdReturns400(): void
    {
        $accessToken = $this->authenticateWithChatbotAccess();

        $this->postJson(['message' => 'Salut', 'agent_id' => 'unknown'], $accessToken);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    /** @param list<string> $chunks */
    private function yieldChunks(array $chunks): \Generator
    {
        foreach ($chunks as $chunk) {
            yield $chunk;
        }
    }

    private function postJson(array $payload, ?string $token = null): void
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($token) {
            $headers['HTTP_AUTHORIZATION'] = "Bearer $token";
        }

        $this->client->request('POST', self::ENDPOINT, [], [], $headers, json_encode($payload));
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
