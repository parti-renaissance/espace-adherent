<?php

namespace App\Normalizer;

use App\Entity\Coalition\Coalition;
use App\Repository\Coalition\CauseFollowerRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CoalitionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const COALITION_ALREADY_CALLED = 'coalition_normalizer';

    private $causeFollowerRepository;

    public function __construct(CauseFollowerRepository $causeFollowerRepository)
    {
        $this->causeFollowerRepository = $causeFollowerRepository;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::COALITION_ALREADY_CALLED] = true;

        $coalition = $this->normalizer->normalize($object, $format, $context);

        $coalition['cause_followers_count'] = $this->causeFollowerRepository->countByCoalition($object);

        return $coalition;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::COALITION_ALREADY_CALLED])
            && $data instanceof Coalition
            && \in_array('coalition_read', $context['groups'] ?? [])
        ;
    }
}
