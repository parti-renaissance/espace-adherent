<?php

declare(strict_types=1);

namespace Tests\App\Controller\Webhook;

use App\Entity\Timeline\TimelineHiddenFeed;
use App\Repository\Timeline\TimelineHiddenFeedRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractRenaissanceWebTestCase;

#[Group('functional')]
class HideTimelineFeedWebhookControllerTest extends AbstractRenaissanceWebTestCase
{
    private const string URL = '/timeline-feed/hide';

    private ?Connection $connection = null;
    private ?TimelineHiddenFeedRepository $hiddenRepository = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('webhook_renaissance_host'));

        $this->connection = $this->manager->getConnection();
        $this->hiddenRepository = static::getContainer()->get(TimelineHiddenFeedRepository::class);
        $this->manager->createQuery('DELETE FROM '.TimelineHiddenFeed::class.' h')->execute();
    }

    protected function tearDown(): void
    {
        $this->connection = null;
        $this->hiddenRepository = null;

        parent::tearDown();
    }

    public function testHidesAndDeletesMirrorRow(): void
    {
        $uuid = Uuid::v4();
        $this->insertMirrorRow($uuid);

        $this->postHide(['uuid' => $uuid->toRfc4122()], $this->configuredToken());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(1, $this->countHidden($uuid));
        self::assertSame(0, $this->countMirrorRows($uuid));
    }

    public function testInvalidTokenIsForbidden(): void
    {
        $uuid = Uuid::v4();

        $this->postHide(['uuid' => $uuid->toRfc4122()], 'wrong-token');

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertSame(0, $this->countHidden($uuid));
    }

    public function testMissingTokenIsForbidden(): void
    {
        $uuid = Uuid::v4();

        $this->postHide(['uuid' => $uuid->toRfc4122()], null);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertSame(0, $this->countHidden($uuid));
    }

    public function testInvalidUuidIsBadRequest(): void
    {
        $this->postHide(['uuid' => 'not-a-uuid'], $this->configuredToken());

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testDoubleHideIsIdempotent(): void
    {
        $uuid = Uuid::v4();
        $this->insertMirrorRow($uuid);

        $this->postHide(['uuid' => $uuid->toRfc4122()], $this->configuredToken());
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->postHide(['uuid' => $uuid->toRfc4122()], $this->configuredToken());
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertSame(1, $this->countHidden($uuid));
    }

    private function postHide(array $payload, ?string $token): void
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if (null !== $token) {
            $server['HTTP_X_WEBHOOK_TOKEN'] = $token;
        }

        $this->client->request(Request::METHOD_POST, self::URL, [], [], $server, json_encode($payload));
    }

    /**
     * The configured webhook secret, read from the same env var the controller is bound to — so the test
     * matches whatever value the environment provides instead of hardcoding (and coupling to) it.
     */
    private function configuredToken(): string
    {
        return (string) ($_SERVER['TIMELINE_FEED_HIDE_WEBHOOK_KEY'] ?? $_ENV['TIMELINE_FEED_HIDE_WEBHOOK_KEY'] ?? getenv('TIMELINE_FEED_HIDE_WEBHOOK_KEY'));
    }

    private function insertMirrorRow(Uuid $uuid): void
    {
        $this->connection->executeStatement(
            'INSERT INTO timeline_feed (uuid, type, publication_date, event_date, audience, display, updated_at)
             VALUES (:uuid, :type, :pub, NULL, NULL, :display, :now)',
            [
                'uuid' => $uuid->toRfc4122(),
                'type' => 'event',
                'pub' => new \DateTimeImmutable('2026-05-20 10:00:00'),
                'display' => ['objectID' => $uuid->toRfc4122()],
                'now' => new \DateTimeImmutable(),
            ],
            [
                'pub' => Types::DATETIME_IMMUTABLE,
                'display' => Types::JSON,
                'now' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }

    private function countHidden(Uuid $uuid): int
    {
        return $this->hiddenRepository->count(['uuid' => $uuid]);
    }

    private function countMirrorRows(Uuid $uuid): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM timeline_feed WHERE uuid = :uuid',
            ['uuid' => $uuid->toRfc4122()],
        );
    }
}
