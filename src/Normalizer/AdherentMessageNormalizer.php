<?php

namespace App\Normalizer;

use App\AdherentMessage\StatisticsAggregator;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly StatisticsAggregator $statisticsAggregator,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
    ) {
    }

    /** @param AdherentMessage $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        $groups = $context['groups'] ?? [];

        if (\in_array('message_read_list', $groups, true)) {
            $data['statistics'] = $this->statisticsAggregator->aggregateData($object);
        }

        if (array_intersect($groups, ['message_read_list', 'message_read'])) {
            $data['preview_link'] = $this->mailchimpObjectIdMapping->generateMailchimpPreviewLink($object->getMailchimpId());

            $data['author']['scope'] = $object->getAuthorScope();

            if (!empty($data['sender'])) {
                $data['sender'] = array_merge($data['sender'], [
                    'instance' => $object->getAuthorInstance(),
                    'role' => $object->getAuthorRole(),
                    'zone' => $object->getAuthorZone(),
                    'theme' => $object->getAuthorTheme(),
                ]);
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AdherentMessage::class => false,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof AdherentMessage;
    }
}
