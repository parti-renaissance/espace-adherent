<?php

namespace App\Normalizer;

use App\Entity\Pap\BuildingBlock;
use App\Entity\Pap\Floor;
use App\Repository\Pap\CampaignRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BuildingStatusNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected const ALREADY_CALLED = 'BUILDING_STATUS_NORMALIZER_ALREADY_CALLED';

    private RequestStack $requestStack;
    private CampaignRepository $campaignRepository;

    public function __construct(RequestStack $requestStack, CampaignRepository $campaignRepository)
    {
        $this->requestStack = $requestStack;
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[static::ALREADY_CALLED.'_'.\get_class($object)] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $campaignUuid = $this->requestStack->getMasterRequest()->query->get('campaign_uuid');
        if (!$campaignUuid) {
            return $data;
        }

        $campaign = $this->campaignRepository->findOneByUuid($campaignUuid);
        if (!$campaign) {
            return $data;
        }

        $data['status'] = $object->getStatisticForCampaign($campaign)->getStatus();

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return \is_object($data) && !isset($context[static::ALREADY_CALLED.'_'.\get_class($data)])
            && ($data instanceof Floor || $data instanceof BuildingBlock);
    }
}
