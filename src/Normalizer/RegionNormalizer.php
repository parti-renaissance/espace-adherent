<?php

namespace App\Normalizer;

use App\Entity\Geo\Region;
use App\Entity\Jecoute\Region as RegionCampaign;
use App\Repository\Jecoute\RegionRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RegionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'REGION_NORMALIZER_ALREADY_CALLED';

    private $regionCampaignRepository;

    public function __construct(RegionRepository $regionCampaignRepository)
    {
        $this->regionCampaignRepository = $regionCampaignRepository;
    }

    /**
     * @param Region $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('jecoute_department_read', $context['groups'])) {
            $regionCampaignData = null;
            if ($regionCampaign = $this->findRegionCampaign($object)) {
                $regionCampaignData = $this->normalizer->normalize($regionCampaign, $format, ['groups' => ['jecoute_region_read']]);
            }
        }

        $data['campaign'] = $regionCampaignData;

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Region;
    }

    private function findRegionCampaign(Region $region): ?RegionCampaign
    {
        return $this->regionCampaignRepository->findOneBy(['geoRegion' => $region]);
    }
}
