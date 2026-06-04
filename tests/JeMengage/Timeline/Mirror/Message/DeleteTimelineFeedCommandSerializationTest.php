<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror\Message;

use App\JeMengage\Timeline\Mirror\Message\DeleteTimelineFeedCommand;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * The default transport serialises messages to JSON (symfony_serializer). This proves the
 * Uuid-typed objectId survives the encode/decode round-trip the async transport performs in
 * production — a path the sync test transport never exercises.
 */
class DeleteTimelineFeedCommandSerializationTest extends AbstractKernelTestCase
{
    public function testObjectIdUuidSurvivesTransportRoundTrip(): void
    {
        /** @var SerializerInterface $serializer */
        $serializer = $this->get('messenger.transport.symfony_serializer');

        $uuid = Uuid::v4();
        $encoded = $serializer->encode(new Envelope(new DeleteTimelineFeedCommand($uuid)));
        $decoded = $serializer->decode($encoded)->getMessage();

        self::assertInstanceOf(DeleteTimelineFeedCommand::class, $decoded);
        self::assertTrue($decoded->getUuid()->equals($uuid));
    }
}
