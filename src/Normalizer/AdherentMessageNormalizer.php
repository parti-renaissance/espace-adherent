<?php

namespace App\Normalizer;

use App\AdherentMessage\StatisticsAggregator;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ADHERENT_MESSAGE_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly StatisticsAggregator $statisticsAggregator,
        private readonly MailchimpObjectIdMapping $mailchimpObjectIdMapping,
    ) {
    }

    /** @var AbstractAdherentMessage */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[static::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $groups = $context['groups'] ?? [];

        if (\in_array('message_read_list', $groups, true)) {
            $data['statistics'] = $this->statisticsAggregator->aggregateData($object);
            $data['preview_link'] = $this->mailchimpObjectIdMapping->generateMailchimpPreviewLink($object->getMailchimpId());
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::ALREADY_CALLED])
            && $data instanceof AbstractAdherentMessage;
    }
}
