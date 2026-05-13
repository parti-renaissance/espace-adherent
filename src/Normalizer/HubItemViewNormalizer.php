<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Action\ActionTypeEnum;
use App\Api\DTO\HubItemView;
use App\Entity\Action\Action;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HubItemViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /** @param HubItemView $object */
    public function normalize($object, $format = null, array $context = []): array
    {
        $payloadContext = $context;
        $payloadContext[__CLASS__] = true;

        $rawPayload = $this->normalizer->normalize($object->payload, $format, $payloadContext);

        if (!\is_array($rawPayload)) {
            return ['type' => $object->type];
        }

        if ($object->payload instanceof Action) {
            return $this->shapeAction($object->payload, $rawPayload);
        }

        return ['type' => 'event', ...$rawPayload];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof HubItemView;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [HubItemView::class => true];
    }

    private function shapeAction(Action $action, array $raw): array
    {
        $label = ActionTypeEnum::LABELS[$action->type] ?? null;

        return [
            'type' => 'action',
            'uuid' => $raw['uuid'] ?? null,
            'name' => $label,
            'slug' => null,
            'time_zone' => null,
            'live_url' => null,
            'visibility' => null,
            'created_at' => $raw['created_at'] ?? null,
            'begin_at' => $raw['date'] ?? null,
            'finish_at' => null,
            'organizer' => $raw['author'] ?? null,
            'participants_count' => $raw['participants_count'] ?? null,
            'status' => $raw['status'] ?? null,
            'capacity' => null,
            'post_address' => $raw['post_address'] ?? null,
            'category' => null === $action->type ? null : [
                'event_group_category' => null,
                'description' => null,
                'name' => $label,
                'slug' => $action->type,
            ],
            'visio_url' => null,
            'pinned' => false,
            'hidden' => false,
            'editable' => $raw['editable'] ?? false,
            'is_national' => false,
            'mode' => null,
            'local_begin_at' => null,
            'local_finish_at' => null,
            'image_url' => null,
            'image' => null,
            'user_registered_at' => $raw['user_registered_at'] ?? null,
        ];
    }
}
