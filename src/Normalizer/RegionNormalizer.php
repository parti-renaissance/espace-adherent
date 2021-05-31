<?php

namespace App\Normalizer;

use App\Entity\Geo\Region;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Region as RegionCampaign;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Jecoute\RegionRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RegionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'REGION_NORMALIZER_ALREADY_CALLED';

    private $regionCampaignRepository;
    private $zoneRepository;

    public function __construct(RegionRepository $regionCampaignRepository, ZoneRepository $zoneRepository)
    {
        $this->regionCampaignRepository = $regionCampaignRepository;
        $this->zoneRepository = $zoneRepository;
    }

    /**
     * @param Region $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        $regionCampaignData = null;

        if (\in_array('jecoute_department_read', $context['groups'] ?? []) && isset($context['postal_code'])) {
            if ($regionCampaign = $this->findRegionCampaign($object, $context['postal_code'])) {
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

    private function findRegionCampaign(Region $region, string $postalCode): ?RegionCampaign
    {
        $department = null;
        $zone = $this->zoneRepository->findOneByPostalCode($postalCode);
        $geoZoneRegion = $this->zoneRepository->findGeoZoneByGeoRegion($region);

        if (null !== $zone) {
            $department = current($zone->getParentsOfType(Zone::DEPARTMENT));
        }

        if ($department) {
            return $this->regionCampaignRepository->findOneCampaignByZone($geoZoneRegion, $department, $postalCode);
        }

        return $this->regionCampaignRepository->findOneBy(['zone' => $geoZoneRegion]);
    }
}
