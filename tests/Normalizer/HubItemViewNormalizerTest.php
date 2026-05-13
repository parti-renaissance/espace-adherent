<?php

declare(strict_types=1);

namespace Tests\App\Normalizer;

use App\Action\ActionTypeEnum;
use App\Api\DTO\HubItemView;
use App\Entity\Action\Action;
use App\Entity\Event\Event;
use App\Normalizer\HubItemViewNormalizer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Group('unit')]
class HubItemViewNormalizerTest extends TestCase
{
    private HubItemViewNormalizer $normalizer;
    private NormalizerInterface $innerNormalizer;

    protected function setUp(): void
    {
        $this->innerNormalizer = $this->createMock(NormalizerInterface::class);
        $this->normalizer = new HubItemViewNormalizer();
        $this->normalizer->setNormalizer($this->innerNormalizer);
    }

    public function testActionPayloadSurfacesTypeLabelInCategoryName(): void
    {
        $action = new Action();
        $action->type = ActionTypeEnum::PAP;

        $this->innerNormalizer
            ->expects(self::once())
            ->method('normalize')
            ->willReturn([
                'uuid' => 'uuid-1',
                'type' => 'pap',
                'date' => '2026-06-01T10:00:00+02:00',
                'created_at' => '2026-05-01T10:00:00+02:00',
                'status' => 'scheduled',
                'participants_count' => 3,
                'author' => ['uuid' => 'author-uuid'],
                'post_address' => ['address' => 'somewhere'],
                'editable' => false,
                'user_registered_at' => null,
            ])
        ;

        $output = $this->normalizer->normalize(new HubItemView('action', $action));

        // Top-level name carries the human label, not the raw enum code.
        self::assertSame('porte à porte', $output['name']);

        // category.name carries the same label; category.slug carries the raw enum code.
        self::assertSame(
            [
                'event_group_category' => null,
                'description' => null,
                'name' => 'porte à porte',
                'slug' => 'pap',
            ],
            $output['category']
        );
    }

    public function testActionPayloadShapeMirrorsEventCanonicalFields(): void
    {
        $action = new Action();
        $action->type = ActionTypeEnum::TRACTAGE;

        $this->innerNormalizer
            ->method('normalize')
            ->willReturn([
                'uuid' => 'uuid-2',
                'type' => 'tractage',
                'date' => '2026-06-01T10:00:00+02:00',
                'created_at' => '2026-05-01T10:00:00+02:00',
                'status' => 'scheduled',
                'participants_count' => 0,
                'author' => null,
                'post_address' => null,
                'editable' => true,
                'user_registered_at' => '2026-05-02T10:00:00+02:00',
            ])
        ;

        $output = $this->normalizer->normalize(new HubItemView('action', $action));

        self::assertSame('action', $output['type']);
        self::assertSame('2026-06-01T10:00:00+02:00', $output['begin_at']);
        self::assertNull($output['finish_at']);
        self::assertNull($output['slug']);
        self::assertNull($output['time_zone']);
        self::assertNull($output['mode']);
        self::assertNull($output['visibility']);
        self::assertFalse($output['is_national']);
        self::assertFalse($output['hidden']);
        self::assertFalse($output['pinned']);
        self::assertTrue($output['editable']);
        self::assertSame('2026-05-02T10:00:00+02:00', $output['user_registered_at']);
    }

    public function testEventPayloadIsPassedThroughWithTypePrefix(): void
    {
        $event = $this->createStub(Event::class);

        $this->innerNormalizer
            ->expects(self::once())
            ->method('normalize')
            ->willReturn([
                'uuid' => 'event-uuid',
                'name' => 'Some Event',
                'begin_at' => '2026-06-01T10:00:00+02:00',
            ])
        ;

        $output = $this->normalizer->normalize(new HubItemView('event', $event));

        self::assertSame('event', $output['type']);
        self::assertSame('event-uuid', $output['uuid']);
        self::assertSame('Some Event', $output['name']);
    }

    public function testSupportsHubItemViewAndRejectsRawEntities(): void
    {
        $view = new HubItemView('event', $this->createStub(Event::class));

        self::assertTrue($this->normalizer->supportsNormalization($view));
        self::assertFalse($this->normalizer->supportsNormalization($this->createStub(Event::class)));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }
}
