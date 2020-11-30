<?php

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Repository\Jecoute\DataSurveyRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class JecouteAdherentNormalizer extends AbstractAdherentNormalizer
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'JECOUTE_ADHERENT_NORMALIZER_ALREADY_CALLED';

    private $dataSurveyRepository;

    public function __construct(DataSurveyRepository $dataSurveyRepository)
    {
        $this->dataSurveyRepository = $dataSurveyRepository;
    }

    /**
     * @param Adherent $object
     */
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
            && \in_array('jemarche_user_profile', $context['groups'])
        ;
    }
}
