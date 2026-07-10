<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadPollData;
use App\Entity\Poll\Poll;
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
class PollControllerTest extends AbstractApiTestCase
{
    use ApiControllerTestTrait;
    use ControllerTestTrait;

    private const UNKNOWN_UUID = '00000000-0000-4000-8000-000000000000';

    private ?string $accessToken = null;

    public function testGetCurrentPollReturnsActivePoll(): void
    {
        $data = $this->getJson('/api/v3/polls/current');

        self::assertIsArray($data);
        self::assertSame('in_progress', $data['state']);
        self::assertArrayNotHasKey('enabled', $data);
        self::assertArrayHasKey('choices', $data);
        self::assertArrayNotHasKey('result', $data);
        self::assertArrayHasKey('participants', $data);
        self::assertFalse($data['has_voted']);
        self::assertNull($data['voted_choice']);
        self::assertNull($data['voted_at']);
    }

    public function testGetCurrentPollReturnsNoContentWhenNoActivePoll(): void
    {
        foreach ($this->manager->getRepository(Poll::class)->findAll() as $poll) {
            $poll->setPublished(false);
        }
        $this->manager->flush();

        $this->client->request(Request::METHOD_GET, '/api/v3/polls/current', [], [], $this->authorizationHeader());

        self::assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        self::assertSame('', $this->client->getResponse()->getContent());
    }

    public function testGetUnpublishedPollByUuidReturnsNotFound(): void
    {
        $poll = $this->manager->getRepository(Poll::class)->findOneByUuid(LoadPollData::POLL_01_UUID);
        $poll->setPublished(false);
        $this->manager->flush();

        $this->client->request(Request::METHOD_GET, '/api/v3/polls/'.LoadPollData::POLL_01_UUID, [], [], $this->authorizationHeader());

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testGetFinishedPollExposesResultsAndParticipants(): void
    {
        $data = $this->getJson('/api/v3/polls/'.LoadPollData::POLL_06_UUID);

        self::assertSame(LoadPollData::POLL_06_UUID, $data['uuid']);
        self::assertSame('finished', $data['state']);
        self::assertArrayHasKey('result', $data);
        self::assertSame(4, $data['result']['total']);
        self::assertSame(4, $data['participant_count']);

        self::assertCount(3, $data['participants']);
        foreach ($data['participants'] as $participant) {
            self::assertArrayHasKey('first_name', $participant);
            self::assertStringContainsString('/images/profile/', $participant['image_url']);
        }
    }

    public function testGetUpcomingPollHidesResultsAndParticipants(): void
    {
        $data = $this->getJson('/api/v3/polls/'.LoadPollData::POLL_07_UUID);

        self::assertSame('upcoming', $data['state']);
        self::assertArrayNotHasKey('result', $data);
        self::assertArrayNotHasKey('participants', $data);
        self::assertSame(0, $data['participant_count']);
    }

    public function testGetUnknownPollReturnsNotFound(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/v3/polls/'.self::UNKNOWN_UUID, [], [], $this->authorizationHeader());

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testVoteOnActivePollReturnsCreated(): void
    {
        $this->postJson('/api/v3/polls/'.LoadPollData::POLL_01_UUID.'/reply', ['choice' => LoadPollData::POLL_01_CHOICE_01_UUID]);

        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $data = $this->getJson('/api/v3/polls/'.LoadPollData::POLL_01_UUID);
        self::assertSame(5, $data['participant_count']);
        self::assertSame(5, $data['result']['total']);
        self::assertTrue($data['has_voted']);
        self::assertSame(LoadPollData::POLL_01_CHOICE_01_UUID, $data['voted_choice']);
        self::assertNotNull($data['voted_at']);
        self::assertNotFalse(\DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339, $data['voted_at']));
    }

    public function testVotingTwiceOnSamePollReturnsConflict(): void
    {
        $this->postJson('/api/v3/polls/'.LoadPollData::POLL_01_UUID.'/reply', ['choice' => LoadPollData::POLL_01_CHOICE_01_UUID]);
        self::assertSame(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        $this->postJson('/api/v3/polls/'.LoadPollData::POLL_01_UUID.'/reply', ['choice' => LoadPollData::POLL_01_CHOICE_02_UUID]);
        self::assertSame(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
    }

    public function testVoteOnFinishedPollReturnsConflict(): void
    {
        $this->postJson('/api/v3/polls/'.LoadPollData::POLL_06_UUID.'/reply', ['choice' => LoadPollData::POLL_06_CHOICE_01_UUID]);

        self::assertSame(Response::HTTP_CONFLICT, $this->client->getResponse()->getStatusCode());
    }

    public function testVoteWithChoiceFromAnotherPollReturnsNotFound(): void
    {
        $this->postJson('/api/v3/polls/'.LoadPollData::POLL_01_UUID.'/reply', ['choice' => LoadPollData::POLL_06_CHOICE_01_UUID]);

        self::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_13_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ15',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'president-ad@renaissance-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );
    }

    private function authorizationHeader(): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer '.$this->accessToken];
    }

    private function getJson(string $url): array
    {
        $this->client->request(Request::METHOD_GET, $url, [], [], $this->authorizationHeader());

        $response = $this->client->getResponse();
        self::assertTrue($response->isSuccessful(), 'Request failed: '.$response->getContent());

        return json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    private function postJson(string $url, array $body): void
    {
        $this->client->request(
            Request::METHOD_POST,
            $url,
            [],
            [],
            $this->authorizationHeader() + ['CONTENT_TYPE' => 'application/json'],
            json_encode($body, \JSON_THROW_ON_ERROR)
        );
    }
}
