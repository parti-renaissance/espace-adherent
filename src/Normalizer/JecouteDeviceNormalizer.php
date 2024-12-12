<?php

namespace App\Normalizer;

use App\Repository\Jecoute\JemarcheDataSurveyRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class JecouteDeviceNormalizer extends DeviceNormalizer
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'JECOUTE_DEVICE_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly JemarcheDataSurveyRepository $dataSurveyRepository)
    {
    }

    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = parent::normalize($object, $format, $context);

        $data['surveys'] = [
            'total' => $this->dataSurveyRepository->countByDevice($object),
            'last_month' => $this->dataSurveyRepository->countByDeviceForLastMonth($object),
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return parent::supportsNormalization($data, $format, $context)
            && \in_array('jemarche_user_profile', $context['groups'] ?? []);
    }
}
