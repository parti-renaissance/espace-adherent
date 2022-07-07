<?php

namespace App\Normalizer;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\AdherentMessage\StatisticsAggregator;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdherentMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'ADHERENT_MESSAGE_NORMALIZER_ALREADY_CALLED';

    private StatisticsAggregator $statisticsAggregator;

    public function __construct(StatisticsAggregator $statisticsAggregator)
    {
        $this->statisticsAggregator = $statisticsAggregator;
    }

    /** @var AbstractAdherentMessage */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[static::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $groups = $context['groups'] ?? [];

        if (\in_array('message_read_list', $groups, true)) {
            $data['statistics'] = $this->statisticsAggregator->aggregateData($object);
            $data['zones'] = $this->normalizer->normalize(
                $this->getZonesFromMessage($object),
                $format,
                array_merge($context, ['groups' => ['zone_read']])
            );
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::ALREADY_CALLED])
            && $data instanceof AbstractAdherentMessage
        ;
    }

    private function getZonesFromMessage(AbstractAdherentMessage $message): array
    {
        $author = $message->getAuthor();

        switch ($message->getType()) {
            case AdherentMessageTypeEnum::REFERENT && $author->isReferent():
                return $author->getManagedArea()->getZones()->toArray();
            case AdherentMessageTypeEnum::CANDIDATE && $author->isCandidate():
                return [$author->getCandidateManagedArea()->getZone()];
            case AdherentMessageTypeEnum::SENATOR && $author->isSenator():
                return [$author->getSenatorArea()->getDepartmentTag()->getZone()];
            case AdherentMessageTypeEnum::DEPUTY && $author->isDeputy():
                return [$author->getDeputyZone()];
            case AdherentMessageTypeEnum::CORRESPONDENT && $author->isCorrespondent():
                return [$author->getCorrespondentZone()];
            default:
                return [];
        }
    }
}
