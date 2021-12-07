<?php

namespace App\Normalizer\Pap;

use App\Entity\Pap\Floor;
use App\Entity\Pap\FloorStatistics;
use App\Repository\Pap\CampaignRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BuildingFloorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'FLOOR_NORMALIZER_ALREADY_CALLED';

    private RequestStack $requestStack;
    private CampaignRepository $campaignRepository;

    public function __construct(RequestStack $requestStack, CampaignRepository $campaignRepository)
    {
        $this->requestStack = $requestStack;
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @param Floor $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[static::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $campaign = null;
        if (!empty($context['campaign'])) {
            $campaign = $context['campaign'];
        } else {
            if ($campaignUuid = $this->requestStack->getMasterRequest()->query->get('campaign_uuid')) {
                $campaign = $this->campaignRepository->findOneByUuid($campaignUuid);
            }
        }

        /** @var FloorStatistics $stats */
        if ($campaign && $stats = $object->findStatisticsForCampaign($campaign)) {
            $data['visited_doors'] = $stats->getVisitedDoors();
            $data['nb_surveys'] = $stats->getNbSurveys();
            $data['status'] = $stats->getStatus();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $data instanceof Floor && !isset($context[static::ALREADY_CALLED]);
    }
}
