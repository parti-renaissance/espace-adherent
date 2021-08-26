<?php

namespace App\Normalizer;

use App\Repository\Jecoute\JemarcheDataSurveyRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class JecouteDeviceNormalizer extends DeviceNormalizer
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'JECOUTE_DEVICE_NORMALIZER_ALREADY_CALLED';

    private $dataSurveyRepository;

    public function __construct(JemarcheDataSurveyRepository $dataSurveyRepository)
    {
        $this->dataSurveyRepository = $dataSurveyRepository;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        $data['surveys'] = [
            'total' => $this->dataSurveyRepository->countByDevice($object),
            'last_month' => $this->dataSurveyRepository->countByDeviceForLastMonth($object),
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return parent::supportsNormalization($data, $format, $context)
            && \in_array('jemarche_user_profile', $context['groups'] ?? [])
        ;
    }
}
