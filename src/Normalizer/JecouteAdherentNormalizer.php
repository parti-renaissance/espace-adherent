<?php

namespace App\Normalizer;

use App\Repository\Jecoute\DataSurveyRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class JecouteAdherentNormalizer extends AdherentNormalizer
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'JECOUTE_ADHERENT_NORMALIZER_ALREADY_CALLED';

    private $dataSurveyRepository;

    public function __construct(DataSurveyRepository $dataSurveyRepository)
    {
        $this->dataSurveyRepository = $dataSurveyRepository;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        $data['surveys'] = [
            'total' => $this->dataSurveyRepository->countByAdherent($object),
            'last_month' => $this->dataSurveyRepository->countByAdherentForLastMonth($object),
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
